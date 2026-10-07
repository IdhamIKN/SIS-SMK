<?php

namespace App\Console\Commands;

use App\Models\JadwalKBM;
use App\Models\LaporanKehadiranGuru;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckKelasOrange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sis:check-orange';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek jam pelajaran yang sudah lewat 20 menit tanpa laporan, set status orange';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan kelas orange...');

        $hariIni = Carbon::now()->toDateString();
        $hari = Carbon::now()->locale('id')->dayName;
        $waktuSekarang = Carbon::now();

        Log::channel('sis')->info('[CheckOrange] Mulai pengecekan', [
            'tanggal' => $hariIni,

            'hari' => $hari,
            'waktu_sekarang' => $waktuSekarang->format('H:i:s'),
        ]);

        // Ambil semua jadwal hari ini
        $jadwalHariIni = JadwalKBM::with(['gtk', 'kelas'])
            ->where('hari', $hari)
            ->get();

        $this->info("Ditemukan {$jadwalHariIni->count()} jadwal hari ini");

        $counter = 0;

        foreach ($jadwalHariIni as $jadwal) {
            // Cek apakah waktu sekarang sudah lewat jam selesai + 20 menit
            $jamSelesai = null;

            try {
                // `jam_selesai` di model sudah cast menjadi `datetime:H:i`,
                // tapi kita tetap buat parsing robust (bisa saja ada data tidak konsisten).
                $jamSelesaiValue = $jadwal->jam_selesai;

                if ($jamSelesaiValue instanceof \Carbon\CarbonInterface) {
                    $jamSelesai = Carbon::instance($jamSelesaiValue);
                } elseif ($jamSelesaiValue instanceof \DateTimeInterface) {
                    $jamSelesai = Carbon::instance($jamSelesaiValue);
                } elseif (is_string($jamSelesaiValue)) {
                    $jamSelesaiValue = trim($jamSelesaiValue);

                    if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $jamSelesaiValue)) {
                        $jamSelesai = Carbon::createFromFormat('H:i:s', $jamSelesaiValue);
                    } elseif (preg_match('/^\d{2}:\d{2}$/', $jamSelesaiValue)) {
                        $jamSelesai = Carbon::createFromFormat('H:i', $jamSelesaiValue);
                    } else {
                        throw new \InvalidArgumentException("Format jam_selesai tidak dikenali: {$jamSelesaiValue}");
                    }
                } else {
                    throw new \InvalidArgumentException('jam_selesai bertipe tidak didukung');
                }

                $batasWaktu = $jamSelesai->copy()->addMinutes(20);
            } catch (\Throwable $e) {
                Log::channel('sis')->error('[CheckOrange] Parsing jam selesai gagal, skip jadwal', [

                    'jadwal_id' => $jadwal->id,
                    'raw_jam_selesai' => $jadwal->jam_selesai,
                    'error' => $e->getMessage(),
                ]);

                $this->warn("Skip jadwal id {$jadwal->id} karena jam_selesai tidak valid");
                continue;
            }


            if ($waktuSekarang->greaterThan($batasWaktu)) {
                // Cek apakah sudah ada laporan untuk jadwal ini hari ini
                $sudahAdaLaporan = LaporanKehadiranGuru::where('jadwal_kbm_id', $jadwal->id)
                    ->where('tanggal', $hariIni)
                    ->exists();

                if (! $sudahAdaLaporan) {
                    // Buat laporan dengan status orange
                    try {
                        $laporan = LaporanKehadiranGuru::create([
                            'jadwal_kbm_id' => $jadwal->id,
                            'gtk_id' => $jadwal->gtk_id,
                            'kelas_id' => $jadwal->kelas_id,
                            'tanggal' => $hariIni,
                            'jam_ke' => $jadwal->jam_ke,
                            'status' => 'orange',
                            'waktu_laporan' => $waktuSekarang,
                            'catatan' => 'Auto-generated: Tidak ada laporan setelah 20 menit jam pelajaran selesai',
                        ]);

                        Log::channel('sis')->warning('[CheckOrange] Status orange dibuat', [

                            'jadwal_id' => $jadwal->id,
                            'gtk_nama' => $jadwal->gtk->nama_lengkap,
                            'kelas' => $jadwal->kelas->nama_kelas,
                            'jam_ke' => $jadwal->jam_ke,
                            'laporan_id' => $laporan->id,
                        ]);

                        $counter++;

                        $this->line("✓ Status orange untuk {$jadwal->gtk->nama_lengkap} - {$jadwal->kelas->nama_kelas} Jam {$jadwal->jam_ke}");
                    } catch (\Exception $e) {
                        Log::channel('sis')->error('[CheckOrange] Gagal buat laporan orange', [

                            'jadwal_id' => $jadwal->id,
                            'error' => $e->getMessage(),
                        ]);

                        $this->error("✗ Gagal buat laporan untuk {$jadwal->gtk->nama_lengkap} - {$jadwal->kelas->nama_kelas}");
                    }
                }
            }
        }

        Log::channel('sis')->info('[CheckOrange] Selesai', [

            'total_orange_dibuat' => $counter,
        ]);

        $this->info("Selesai! {$counter} laporan orange dibuat.");
    }
}
