<?php

namespace App\Console\Commands;

use App\Models\AbsenEvent;
use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\Event;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoPointPelanggaranEvent extends Command
{
    protected $signature = 'event:auto-point-pelanggaran
                            {--event= : ID event spesifik (opsional)}';

    protected $description = 'Berikan poin pelanggaran otomatis kepada siswa yang tidak scan masuk pada event yang sudah selesai';

    /** Shortcut log ke channel point-pelanggaran */
    private function plog(string $level, string $message, array $context = []): void
    {
        Log::channel('point-pelanggaran')->{$level}($message, $context);
    }

    public function handle(): int
    {
        $specificEventId = $this->option('event');
        $runAt           = now()->toDateTimeString();

        // Cek mode libur panjang — skip semua pemrosesan
        $sekolah = \App\Models\Sekolah::aktif();
        if ($sekolah?->sedangLibur()) {
            $this->info('Mode Libur Panjang aktif, Auto Point Pelanggaran Event dilewati.');
            $this->plog('info', 'Mode Libur Panjang aktif, command dilewati.', [
                'run_at'       => $runAt,
                'libur_dari'   => optional($sekolah->libur_dari)->toDateString(),
                'libur_sampai' => optional($sekolah->libur_sampai)->toDateString(),
            ]);
            return self::SUCCESS;
        }

        $gracePeriodMinutes = 10;
        $cutoff             = now()->subMinutes($gracePeriodMinutes);
        // Safety: jangan proses event yang tanggal_selesainya lebih dari 3 hari yang lalu
        // dan baru saja di-generate (created_at < 1 jam yang lalu tapi tanggal sudah lampau > 3 hari).
        // Ini mencegah event yang ter-regenerate secara tidak sengaja langsung memproses poin.
        $safetyMaxAgeDays   = 3;

        $this->plog('info', '========== AUTO POINT PELANGGARAN DIMULAI ==========', [
            'run_at'             => $runAt,
            'specific_event'     => $specificEventId ?? 'semua',
            'grace_period_menit' => $gracePeriodMinutes,
            'cutoff_waktu'       => $cutoff->toDateTimeString(),
        ]);

        $query = Event::query()
            ->where('auto_point_pelanggaran', true)
            ->whereNotNull('pasal_pelanggaran_id')
            ->whereNull('auto_point_processed_at')
            ->where('tanggal_selesai', '<', $cutoff)
            // Safety valve 1: event harus sudah dibuat minimal 30 menit lalu
            // (mencegah event yang baru di-regenerate langsung diproses)
            ->where('created_at', '<', now()->subMinutes(30))
            // Safety valve 2: jangan proses event yang tanggal_selesai > 3 hari lalu
            // (mencegah regenerated event lama memproses poin secara retroaktif)
            // Dapat di-override dengan --event=ID untuk kasus manual
            ->when(! $specificEventId, fn ($q) => $q->where(
                'tanggal_selesai', '>=', now()->subDays($safetyMaxAgeDays)
            ));

        if ($specificEventId) {
            $query->where('id', $specificEventId);
        }

        $events = $query->with(['pasalPelanggaran'])->get();

        if ($events->isEmpty()) {
            $this->info('Tidak ada event yang perlu diproses.');
            $this->plog('info', "Tidak ada event yang memenuhi syarat untuk diproses (grace period {$gracePeriodMinutes} menit, cutoff: {$cutoff->toDateTimeString()}).", [
                'run_at'             => $runAt,
                'grace_period_menit' => $gracePeriodMinutes,
                'cutoff_waktu'       => $cutoff->toDateTimeString(),
            ]);
            $this->plog('info', '---------- SELESAI ----------');
            return self::SUCCESS;
        }

        $this->info("Memproses {$events->count()} event...");
        $this->plog('info', "Ditemukan {$events->count()} event untuk diproses (grace period {$gracePeriodMinutes} menit, cutoff: {$cutoff->toDateTimeString()}).", [
            'event_ids'          => $events->pluck('id')->toArray(),
            'grace_period_menit' => $gracePeriodMinutes,
            'cutoff_waktu'       => $cutoff->toDateTimeString(),
        ]);

        foreach ($events as $event) {
            $this->processEvent($event);
        }

        $this->info('Selesai.');
        $this->plog('info', '========== AUTO POINT PELANGGARAN SELESAI ==========');

        return self::SUCCESS;
    }

    protected function processEvent(Event $event): void
    {
        $pasal       = $event->pasalPelanggaran;
        $tanggalEvent = Carbon::parse($event->tanggal_mulai)->toDateString();

        $this->plog('info', "── Memproses Event #{$event->id}: \"{$event->nama_event}\"", [
            'event_id'      => $event->id,
            'nama_event'    => $event->nama_event,
            'tanggal_mulai' => $event->tanggal_mulai,
            'tanggal_selesai' => $event->tanggal_selesai,
            'pasal_id'      => $event->pasal_pelanggaran_id,
        ]);

        // Validasi pasal
        if (! $pasal) {
            $this->warn("  [SKIP] Event #{$event->id}: pasal tidak ditemukan.");
            $this->plog('warning', "SKIP Event #{$event->id}: pasal_pelanggaran_id tidak ditemukan di database.", [
                'event_id'   => $event->id,
                'pasal_id'   => $event->pasal_pelanggaran_id,
            ]);
            return;
        }

        $this->plog('info', "  Pasal: [{$pasal->idpasal}] {$pasal->pasal} — poin default: {$pasal->poin_default}");

        // Ambil peserta
        $pesertaIds = $this->getPesertaIds($event);

        $this->plog('info', "  Total peserta event: " . count($pesertaIds), [
            'berlaku_untuk_semua' => $event->berlaku_untuk_semua,
            'mode_peserta'        => $event->mode_peserta,
        ]);

        if (empty($pesertaIds)) {
            $this->line("  [SKIP] Event #{$event->id}: tidak ada peserta.");
            $this->plog('warning', "SKIP Event #{$event->id}: tidak ada peserta ditemukan.");
            $event->update(['auto_point_processed_at' => now()]);
            return;
        }

        // Siswa yang sudah scan masuk (pola unified: cek waktu_masuk terisi)
        $siswaYangScan = AbsenEvent::where('event_id', $event->id)
            ->whereNotNull('waktu_masuk')
            ->pluck('siswa_id')
            ->toArray();

        $siswaYangTidakScan = array_diff($pesertaIds, $siswaYangScan);

        $this->plog('info', "  Scan masuk: " . count($siswaYangScan) . " siswa | Tidak scan: " . count($siswaYangTidakScan) . " siswa", [
            'jumlah_scan'       => count($siswaYangScan),
            'jumlah_tidak_scan' => count($siswaYangTidakScan),
        ]);

        if (empty($siswaYangTidakScan)) {
            $this->line("  [OK] Event #{$event->id} '{$event->nama_event}': semua siswa sudah scan.");
            $this->plog('info', "OK Event #{$event->id}: semua peserta sudah scan masuk, tidak ada poin diberikan.");
            $event->update(['auto_point_processed_at' => now()]);
            return;
        }

        $tahunAjaran = $this->getTahunAjaran();
        $this->plog('info', "  Tahun ajaran aktif: {$tahunAjaran}");

        $diberikan        = 0;
        $dilewati         = 0;
        $dilewatiDuplikat = 0;
        $dilewatiAbsen    = 0;
        $dilewatiTidakAda = 0;
        $gagal            = 0;

        foreach ($siswaYangTidakScan as $siswaId) {

            // Cek duplikasi
            $sudahAda = Pelanggaran::where('deviceid', 'auto-event-' . $event->id)
                ->where('siswa_id', $siswaId)
                ->exists();

            if ($sudahAda) {
                $dilewatiDuplikat++;
                $dilewati++;
                $this->plog('debug', "  SKIP siswa #{$siswaId}: sudah punya record pelanggaran dari event ini (duplikat).");
                continue;
            }

            // Cek absensi harian — hanya proses jika hadir di sekolah (pola unified)
            $absenHarian = AbsenSiswa::where('siswa_id', $siswaId)
                ->whereDate('tanggal', $tanggalEvent)
                ->first();

            $statusMasuk = $absenHarian?->status_masuk ?? $absenHarian?->status;
            if (! $absenHarian || ! in_array($statusMasuk, ['hadir', 'terlambat'])) {
                $statusAbsen = $statusMasuk ?? 'tidak ada data absen';
                $dilewatiAbsen++;
                $dilewati++;
                $this->plog('debug', "  SKIP siswa #{$siswaId}: status absensi harian = \"{$statusAbsen}\", tidak hadir di sekolah.");
                continue;
            }

            // Ambil data siswa
            $siswa = Siswa::find($siswaId);
            if (! $siswa) {
                $dilewatiTidakAda++;
                $dilewati++;
                $this->plog('warning', "  SKIP siswa #{$siswaId}: data siswa tidak ditemukan di tabel siswas.");
                continue;
            }

            // Buat pelanggaran
            try {
                DB::transaction(function () use ($event, $siswa, $pasal, $tahunAjaran) {
                    // Gunakan poin override jika di-set, fallback ke poin_default pasal
                    $poin = $event->poin_pelanggaran_event ?? $pasal->poin_default;

                    // Pelanggaran::create() akan men-trigger PelanggaranObserver::created()
                    // yang otomatis membuat transaksi di tbltransaksi.
                    // Jika observer gagal, exception akan melempar ke sini dan rollback transaksi.
                    Pelanggaran::create([
                        'siswa_id'     => $siswa->id,
                        'tgl'          => now(),
                        'tahun_ajaran' => $tahunAjaran,
                        'deviceid'     => 'auto-event-' . $event->id,
                        'noreg'        => $siswa->nis ?? '',
                        'nama'         => $siswa->nama_lengkap,
                        'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                        'idpasal'      => $pasal->idpasal,
                        'isi'          => 'Tidak hadir pada event: ' . $event->nama_event,
                        'poin'         => $poin,
                        'pelapor'      => 'Sistem',
                        'created_by'   => $event->created_by,
                    ]);
                });

                $diberikan++;
                $this->plog('info', "  POIN DIBERIKAN → {$siswa->nama_lengkap} ({$siswa->nis}) kelas {$siswa->kelas?->nama_kelas}", [
                    'siswa_id'  => $siswa->id,
                    'nama'      => $siswa->nama_lengkap,
                    'nis'       => $siswa->nis,
                    'kelas'     => $siswa->kelas?->nama_kelas,
                    'pasal'     => $pasal->idpasal,
                    'poin'      => $event->poin_pelanggaran_event ?? $pasal->poin_default,
                    'sumber'    => $event->poin_pelanggaran_event ? 'override_event' : 'poin_default_pasal',
                ]);

            } catch (\Throwable $e) {
                $gagal++;
                $this->plog('error', "  GAGAL buat pelanggaran siswa #{$siswa->id} ({$siswa->nama_lengkap}): " . $e->getMessage(), [
                    'siswa_id'  => $siswa->id,
                    'event_id'  => $event->id,
                    'exception' => $e->getMessage(),
                    'trace'     => $e->getTraceAsString(),
                ]);
            }
        }

        // Tandai event sudah diproses
        $event->update(['auto_point_processed_at' => now()]);

        // Ringkasan
        $this->line("  [DONE] Event #{$event->id} '{$event->nama_event}': {$diberikan} poin diberikan, {$dilewati} dilewati.");

        $this->plog('info', "RINGKASAN Event #{$event->id}: \"{$event->nama_event}\"", [
            'event_id'             => $event->id,
            'nama_event'           => $event->nama_event,
            'pasal'                => "[{$pasal->idpasal}] {$pasal->pasal}",
            'poin_per_siswa'       => $event->poin_pelanggaran_event ?? $pasal->poin_default,
            'poin_sumber'          => $event->poin_pelanggaran_event ? 'override_event' : 'poin_default_pasal',
            'total_peserta'        => count($pesertaIds),
            'total_scan_masuk'     => count($siswaYangScan),
            'total_tidak_scan'     => count($siswaYangTidakScan),
            'poin_diberikan'       => $diberikan,
            'dilewati_total'       => $dilewati,
            'dilewati_duplikat'    => $dilewatiDuplikat,
            'dilewati_tidak_hadir' => $dilewatiAbsen,
            'dilewati_data_hilang' => $dilewatiTidakAda,
            'gagal'                => $gagal,
            'processed_at'         => now()->toDateTimeString(),
        ]);
    }

    protected function getPesertaIds(Event $event): array
    {
        if ($event->berlaku_untuk_semua) {
            return Siswa::where('status_aktif', true)->pluck('id')->toArray();
        }

        if ($event->mode_peserta === 'kelas') {
            $kelasIds = $event->kelas()->pluck('kelas.id')->toArray();
            return Siswa::where('status_aktif', true)
                ->whereIn('kelas_id', $kelasIds)
                ->pluck('id')
                ->toArray();
        }

        return $event->siswa()->pluck('siswas.id')->toArray();
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
