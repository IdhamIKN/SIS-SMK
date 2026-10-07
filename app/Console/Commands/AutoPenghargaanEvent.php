<?php

namespace App\Console\Commands;

use App\Models\AbsenEvent;
use App\Models\AcademicYear;
use App\Models\Event;
use App\Models\Penghargaan;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoPenghargaanEvent extends Command
{
    protected $signature = 'event:auto-penghargaan
                            {--event= : ID event spesifik (opsional)}';

    protected $description = 'Berikan poin penghargaan otomatis kepada siswa yang berhasil scan masuk pada event yang sudah selesai';

    /** Shortcut log ke channel sis */
    private function plog(string $level, string $message, array $context = []): void
    {
        Log::channel('sis')->{$level}('[AutoPenghargaan] ' . $message, $context);
    }

    public function handle(): int
    {
        $specificEventId = $this->option('event');
        $runAt           = now()->toDateTimeString();

        // Cek mode libur panjang — skip semua pemrosesan
        $sekolah = \App\Models\Sekolah::aktif();
        if ($sekolah?->sedangLibur()) {
            $this->info('Mode Libur Panjang aktif, Auto Penghargaan Event dilewati.');
            $this->plog('info', 'Mode Libur Panjang aktif, command dilewati.', [
                'run_at'       => $runAt,
                'libur_dari'   => optional($sekolah->libur_dari)->toDateString(),
                'libur_sampai' => optional($sekolah->libur_sampai)->toDateString(),
            ]);
            return self::SUCCESS;
        }

        $this->plog('info', '========== AUTO PENGHARGAAN DIMULAI ==========', [
            'run_at'         => $runAt,
            'specific_event' => $specificEventId ?? 'semua',
        ]);

        $query = Event::query()
            ->where('auto_penghargaan', true)
            ->whereNotNull('pasal_penghargaan_id')
            ->whereNull('auto_penghargaan_processed_at')
            ->where('tanggal_selesai', '<', now())
            // Safety valve 1: event harus sudah dibuat minimal 30 menit lalu
            ->where('created_at', '<', now()->subMinutes(30))
            // Safety valve 2: jangan proses event yang tanggal_selesai > 3 hari lalu secara otomatis
            ->when(! $specificEventId, fn ($q) => $q->where(
                'tanggal_selesai', '>=', now()->subDays(3)
            ));

        if ($specificEventId) {
            $query->where('id', $specificEventId);
        }

        $events = $query->with(['pasalPenghargaan'])->get();

        if ($events->isEmpty()) {
            $this->info('Tidak ada event yang perlu diproses.');
            $this->plog('info', 'Tidak ada event yang memenuhi syarat untuk diproses.', [
                'run_at' => $runAt,
            ]);
            $this->plog('info', '---------- SELESAI ----------');
            return self::SUCCESS;
        }

        $this->info("Memproses {$events->count()} event...");
        $this->plog('info', "Ditemukan {$events->count()} event untuk diproses.", [
            'event_ids' => $events->pluck('id')->toArray(),
        ]);

        foreach ($events as $event) {
            $this->processEvent($event);
        }

        $this->info('Selesai.');
        $this->plog('info', '========== AUTO PENGHARGAAN SELESAI ==========');

        return self::SUCCESS;
    }

    protected function processEvent(Event $event): void
    {
        $pasal        = $event->pasalPenghargaan;
        $tanggalEvent = Carbon::parse($event->tanggal_mulai)->toDateString();

        $this->plog('info', "── Memproses Event #{$event->id}: \"{$event->nama_event}\"", [
            'event_id'        => $event->id,
            'nama_event'      => $event->nama_event,
            'tanggal_mulai'   => $event->tanggal_mulai,
            'tanggal_selesai' => $event->tanggal_selesai,
            'pasal_id'        => $event->pasal_penghargaan_id,
        ]);

        // Validasi pasal
        if (! $pasal) {
            $this->warn("  [SKIP] Event #{$event->id}: pasal penghargaan tidak ditemukan.");
            $this->plog('warning', "SKIP Event #{$event->id}: pasal_penghargaan_id tidak ditemukan di database.", [
                'event_id' => $event->id,
                'pasal_id' => $event->pasal_penghargaan_id,
            ]);
            return;
        }

        $this->plog('info', "  Pasal: [{$pasal->idpasal}] {$pasal->pasal} — poin default: {$pasal->poin_default}");

        // Siswa yang berhasil scan masuk
        $siswaYangScan = AbsenEvent::where('event_id', $event->id)
            ->whereNotNull('waktu_masuk')
            ->pluck('siswa_id')
            ->toArray();

        $this->plog('info', "  Total siswa yang scan masuk: " . count($siswaYangScan));

        if (empty($siswaYangScan)) {
            $this->line("  [OK] Event #{$event->id} '{$event->nama_event}': tidak ada siswa yang scan, tidak ada penghargaan diberikan.");
            $this->plog('info', "OK Event #{$event->id}: tidak ada siswa yang scan masuk.");
            $event->update(['auto_penghargaan_processed_at' => now()]);
            return;
        }

        $tahunAjaran = $this->getTahunAjaran();
        $this->plog('info', "  Tahun ajaran aktif: {$tahunAjaran}");

        $diberikan = 0;
        $dilewati  = 0;
        $gagal     = 0;

        foreach ($siswaYangScan as $siswaId) {

            // Cek duplikasi
            $sudahAda = Penghargaan::where('deviceid', 'auto-event-penghargaan-' . $event->id)
                ->where('siswa_id', $siswaId)
                ->exists();

            if ($sudahAda) {
                $dilewati++;
                $this->plog('debug', "  SKIP siswa #{$siswaId}: sudah punya record penghargaan dari event ini (duplikat).");
                continue;
            }

            // Ambil data siswa
            $siswa = Siswa::find($siswaId);
            if (! $siswa) {
                $dilewati++;
                $this->plog('warning', "  SKIP siswa #{$siswaId}: data siswa tidak ditemukan di tabel siswas.");
                continue;
            }

            // Buat penghargaan
            try {
                DB::transaction(function () use ($event, $siswa, $pasal, $tahunAjaran) {
                    // Gunakan poin override jika di-set, fallback ke poin_default pasal
                    $poin = $event->poin_penghargaan_event ?? $pasal->poin_default;

                    // Penghargaan::create() dengan acc='YA' akan men-trigger
                    // PenghargaanObserver::created() yang otomatis membuat transaksi di tbltransaksi.
                    // Jika observer gagal, exception akan melempar ke sini dan rollback transaksi.
                    Penghargaan::create([
                        'siswa_id'     => $siswa->id,
                        'tgl'          => now(),
                        'tahun_ajaran' => $tahunAjaran,
                        'deviceid'     => 'auto-event-penghargaan-' . $event->id,
                        'noreg'        => $siswa->nis ?? '',
                        'nama'         => $siswa->nama_lengkap,
                        'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                        'idpasal'      => $pasal->idpasal,
                        'isi'          => 'Berhasil hadir pada event: ' . $event->nama_event,
                        'poin'         => $poin,
                        'pelapor'      => 'Sistem',
                        'ket'          => 'Auto dari event',
                        'acc'          => 'YA',
                        'tglacc'       => now(),
                        'nmacc'        => 'Sistem',
                        'created_by'   => $event->created_by,
                    ]);
                });

                $diberikan++;
                $this->plog('info', "  PENGHARGAAN DIBERIKAN → {$siswa->nama_lengkap} ({$siswa->nis}) kelas {$siswa->kelas?->nama_kelas}", [
                    'siswa_id' => $siswa->id,
                    'nama'     => $siswa->nama_lengkap,
                    'nis'      => $siswa->nis,
                    'kelas'    => $siswa->kelas?->nama_kelas,
                    'pasal'    => $pasal->idpasal,
                    'poin'     => $event->poin_penghargaan_event ?? $pasal->poin_default,
                    'sumber'   => $event->poin_penghargaan_event ? 'override_event' : 'poin_default_pasal',
                ]);

            } catch (\Throwable $e) {
                $gagal++;
                $this->plog('error', "  GAGAL buat penghargaan siswa #{$siswa->id} ({$siswa->nama_lengkap}): " . $e->getMessage(), [
                    'siswa_id'  => $siswa->id,
                    'event_id'  => $event->id,
                    'exception' => $e->getMessage(),
                    'trace'     => $e->getTraceAsString(),
                ]);
            }
        }

        // Tandai event sudah diproses
        $event->update(['auto_penghargaan_processed_at' => now()]);

        // Ringkasan
        $this->line("  [DONE] Event #{$event->id} '{$event->nama_event}': {$diberikan} penghargaan diberikan, {$dilewati} dilewati.");

        $this->plog('info', "RINGKASAN Event #{$event->id}: \"{$event->nama_event}\"", [
            'event_id'              => $event->id,
            'nama_event'            => $event->nama_event,
            'pasal'                 => "[{$pasal->idpasal}] {$pasal->pasal}",
            'poin_per_siswa'        => $event->poin_penghargaan_event ?? $pasal->poin_default,
            'poin_sumber'           => $event->poin_penghargaan_event ? 'override_event' : 'poin_default_pasal',
            'total_scan_masuk'      => count($siswaYangScan),
            'penghargaan_diberikan' => $diberikan,
            'dilewati'              => $dilewati,
            'gagal'                 => $gagal,
            'processed_at'          => now()->toDateTimeString(),
        ]);
    }

    protected function getTahunAjaran(): string
    {
        try {
            $active = AcademicYear::where('is_active', true)->first();
            if ($active && $active->year_start && $active->year_end) {
                return $active->year_start . '/' . $active->year_end;
            }
        } catch (\Throwable) {
            // fallback
        }

        $year  = (int) now()->format('Y');
        $month = (int) now()->format('n');

        return $month < 7
            ? ($year - 1) . '/' . $year
            : $year . '/' . ($year + 1);
    }
}
