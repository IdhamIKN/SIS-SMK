<?php

namespace App\Console\Commands;

use App\Jobs\SendAutoAlfaNotif;
use App\Models\AbsenSiswa;
use App\Models\AcademicYear;
use App\Models\Pelanggaran;
use App\Models\Sekolah;
use App\Models\Siswa;
use App\Models\SubPasal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AutoAlfaSiswa
 *
 * Pola baru: setiap siswa hanya punya 1 record per hari.
 * Command ini:
 *  1. Memastikan record absensi harian sudah ada (firstOrCreate)
 *  2. Mengupdate status_masuk = 'alfa' jika jam_masuk masih kosong
 *     dan batas waktu eksekusi sudah tercapai
 */
class AutoAlfaSiswa extends Command
{
    protected $signature = 'absen:auto-alfa
                            {--date= : Tanggal target format YYYY-MM-DD (default: hari ini)}
                            {--force : Paksa jalan meskipun waktu eksekusi belum tercapai}';

    protected $description = 'Auto-alfa siswa yang belum presensi masuk setelah jam eksekusi yang dikonfigurasi';

    private function slog(string $level, string $message, array $context = []): void
    {
        Log::channel('sis')->{$level}('[AutoAlfa] ' . $message, $context);
    }

    public function handle(): int
    {
        $sekolah = Sekolah::aktif();

        if (! $sekolah?->auto_alfa_enabled) {
            $this->info('Auto Alfa tidak aktif, dilewati.');
            $this->slog('info', 'Auto Alfa tidak aktif di konfigurasi sekolah.');
            return self::SUCCESS;
        }

        $tanggalStr = $this->option('date') ?? now(config('app.timezone', 'Asia/Jakarta'))->toDateString();
        $tanggal    = Carbon::parse($tanggalStr, config('app.timezone', 'Asia/Jakarta'));

        if (! $this->option('force') && $sekolah->sedangLibur($tanggal)) {
            $this->info("Mode Libur Panjang aktif, Auto Alfa dilewati.");
            $this->slog('info', 'Mode Libur Panjang aktif, Auto Alfa dilewati.', ['tanggal' => $tanggalStr]);
            return self::SUCCESS;
        }

        if (! $this->isHariEfektif($tanggal, $sekolah)) {
            $namaHari = $tanggal->locale('id')->isoFormat('dddd');
            $this->info("Hari {$namaHari} bukan hari efektif, Auto Alfa dilewati.");
            $this->slog('info', "Hari {$namaHari} bukan hari efektif, dilewati.", ['tanggal' => $tanggalStr]);
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->sudahWaktunyaEksekusi($sekolah)) {
            $this->info('Jam eksekusi Auto Alfa belum tercapai, dilewati.');
            $this->slog('info', 'Jam eksekusi belum tercapai.', [
                'jam_eksekusi' => $sekolah->jam_eksekusi_auto_alfa,
                'now'          => now()->format('H:i'),
            ]);
            return self::SUCCESS;
        }

        $this->slog('info', '========== AUTO ALFA DIMULAI ==========', [
            'tanggal'      => $tanggalStr,
            'jam_eksekusi' => $sekolah->jam_eksekusi_auto_alfa,
            'auto_poin'    => $sekolah->auto_point_alfa_enabled,
            'pasal_id'     => $sekolah->pasal_alfa_id,
            'wa_notif'     => $sekolah->wa_notif_alfa_enabled,
            'force'        => $this->option('force') ? 'yes' : 'no',
        ]);

        $this->info("Menjalankan Auto Alfa untuk tanggal {$tanggalStr}...");

        $pasal = null;
        if ($sekolah->auto_point_alfa_enabled && $sekolah->pasal_alfa_id) {
            $pasal = SubPasal::where('idpasal', $sekolah->pasal_alfa_id)
                ->orderByDesc('thnajaran')
                ->first();

            if (! $pasal) {
                $this->warn("Pasal ID '{$sekolah->pasal_alfa_id}' tidak ditemukan. Poin pelanggaran tidak akan diberikan.");
                $this->slog('warning', 'Pasal alfa tidak ditemukan.', ['pasal_id' => $sekolah->pasal_alfa_id]);
            }
        }

        $tahunAjaran    = $this->getTahunAjaran();
        $jumlahDibuat   = 0;
        $jumlahDilewati = 0;
        $jumlahPoin     = 0;
        $jumlahWa       = 0;
        $jumlahGagal    = 0;

        Siswa::query()
            ->where('status_aktif', true)
            ->whereNotNull('kelas_id')
            ->with(['kelas'])
            ->orderBy('id')
            ->chunkById(200, function ($siswaList) use (
                $tanggal, $tanggalStr, $sekolah, $pasal, $tahunAjaran,
                &$jumlahDibuat, &$jumlahDilewati, &$jumlahPoin, &$jumlahWa, &$jumlahGagal
            ) {
                foreach ($siswaList as $siswa) {
                    // Cek apakah siswa sudah absen masuk hari ini (jam_masuk terisi)
                    $record = AbsenSiswa::where('siswa_id', $siswa->id)
                        ->whereDate('tanggal', $tanggalStr)
                        ->first();

                    // Jika sudah punya jam_masuk → skip (sudah scan sendiri)
                    if ($record && ! empty($record->jam_masuk)) {
                        $jumlahDilewati++;
                        continue;
                    }

                    // Jika sudah punya status izin/sakit (dari pengajuan izin yang disetujui) → skip
                    // Jangan timpa izin yang sudah ada dengan alfa
                    if ($record && in_array($record->status_masuk ?? $record->status, ['izin', 'sakit'], true)) {
                        $jumlahDilewati++;
                        continue;
                    }

                    try {
                        DB::transaction(function () use (
                            $siswa, $tanggal, $tanggalStr, $sekolah, $pasal, $tahunAjaran,
                            $record, &$jumlahDibuat, &$jumlahPoin, &$jumlahWa
                        ) {
                            $waktuEksekusi = $tanggal->copy()->setTimeFromTimeString(
                                $sekolah->jam_eksekusi_auto_alfa ?? now()->format('H:i:s')
                            );

                            if ($record) {
                                // Record sudah ada tapi belum masuk → update status_masuk = alfa
                                $record->update([
                                    'status_masuk' => 'alfa',
                                    'jam_masuk'    => null,  // tetap kosong (belum scan)
                                    'status'       => 'alfa', // legacy
                                    'waktu_absen'  => $waktuEksekusi, // legacy
                                    'catatan'      => 'Auto Alfa.',
                                ]);
                                $absen = $record;
                            } else {
                                // Buat record baru dengan status alfa
                                $absen = AbsenSiswa::create([
                                    'siswa_id'    => $siswa->id,
                                    'kelas_id'    => $siswa->kelas_id,
                                    'tanggal'     => $tanggalStr,
                                    // jam_masuk dibiarkan null — menandai tidak hadir
                                    'status_masuk' => 'alfa',
                                    'status'       => 'alfa',   // legacy
                                    'jenis'        => 'masuk',  // legacy
                                    'waktu_absen'  => $waktuEksekusi, // legacy
                                    'catatan'      => 'Auto Alfa oleh Sistem.',
                                ]);
                            }

                            $jumlahDibuat++;

                            // Beri poin pelanggaran
                            if ($sekolah->auto_point_alfa_enabled && $pasal) {
                                $deviceId     = 'auto-alfa-' . $tanggalStr;
                                $sudahAdaPoin = Pelanggaran::where('deviceid', $deviceId)
                                    ->where('siswa_id', $siswa->id)
                                    ->exists();

                                if (! $sudahAdaPoin) {
                                    // Pelanggaran::create() men-trigger PelanggaranObserver::created()
                                    // yang otomatis sync ke tbltransaksi.
                                    // Jika observer gagal, exception roll back seluruh transaksi ini.
                                    Pelanggaran::create([
                                        'siswa_id'     => $siswa->id,
                                        'tgl'          => now(),
                                        'tahun_ajaran' => $tahunAjaran,
                                        'deviceid'     => $deviceId,
                                        'noreg'        => $siswa->noreg_legacy ?? $siswa->nis ?? '',
                                        'nama'         => $siswa->nama_lengkap,
                                        'kelas'        => $siswa->kelas?->nama_kelas ?? '',
                                        'idpasal'      => $pasal->idpasal,
                                        'isi'          => 'Alfa — tidak hadir tanpa keterangan',
                                        'poin'         => $pasal->poin_default ?? $pasal->skormin ?? 0,
                                        'pelapor'      => 'Sistem',
                                        'created_by'   => null,
                                    ]);

                                    $jumlahPoin++;
                                }
                            }

                            // Kirim notifikasi WA
                            if ($sekolah->wa_notif_alfa_enabled) {
                                SendAutoAlfaNotif::dispatch($absen);
                                $jumlahWa++;
                            }
                        });

                        $this->slog('info', "ALFA → {$siswa->nama_lengkap} ({$siswa->nis}) kelas {$siswa->kelas?->nama_kelas}", [
                            'siswa_id' => $siswa->id,
                        ]);
                    } catch (\Throwable $e) {
                        $jumlahGagal++;
                        $this->slog('error', "GAGAL siswa #{$siswa->id} ({$siswa->nama_lengkap}): " . $e->getMessage(), [
                            'siswa_id'  => $siswa->id,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->slog('info', '========== AUTO ALFA SELESAI ==========', [
            'tanggal'        => $tanggalStr,
            'dibuat'         => $jumlahDibuat,
            'dilewati'       => $jumlahDilewati,
            'poin_diberikan' => $jumlahPoin,
            'wa_dispatched'  => $jumlahWa,
            'gagal'          => $jumlahGagal,
        ]);

        $this->info(sprintf(
            'Selesai. Alfa dibuat/diupdate: %d | Dilewati (sudah absen): %d | Poin: %d | WA: %d | Gagal: %d',
            $jumlahDibuat, $jumlahDilewati, $jumlahPoin, $jumlahWa, $jumlahGagal
        ));

        return self::SUCCESS;
    }

    private function isHariEfektif(Carbon $tanggal, Sekolah $sekolah): bool
    {
        $map = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        $namaHari  = $map[$tanggal->englishDayOfWeek] ?? '';
        $raw       = $sekolah->hari_efektif;
        $hariAktif = $raw
            ? (is_array($raw) ? $raw : (json_decode($raw, true) ?: []))
            : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        return in_array($namaHari, $hariAktif, true);
    }

    private function sudahWaktunyaEksekusi(Sekolah $sekolah): bool
    {
        $jamEksekusi = $sekolah->jam_eksekusi_auto_alfa;
        if (! $jamEksekusi) {
            return false;
        }

        $timestamp = strtotime((string) $jamEksekusi);
        if ($timestamp === false) {
            return false;
        }

        $waktuEksekusi = Carbon::today(config('app.timezone', 'Asia/Jakarta'))
            ->setTimeFromTimeString(date('H:i:s', $timestamp));

        return now(config('app.timezone', 'Asia/Jakarta'))->gte($waktuEksekusi);
    }

    private function getTahunAjaran(): string
    {
        try {
            $active = AcademicYear::where('is_active', true)->first();
            if ($active && $active->year_start && $active->year_end) {
                return $active->year_start . '/' . $active->year_end;
            }
        } catch (\Throwable) {}

        $year  = (int) now()->format('Y');
        $month = (int) now()->format('n');

        return $month < 7
            ? ($year - 1) . '/' . $year
            : $year . '/' . ($year + 1);
    }
}
