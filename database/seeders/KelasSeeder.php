<?php

namespace Database\Seeders;

use App\Models\GTK;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        Log::channel('sis')->info('[KelasSeeder] Mulai import kelas dari tblkelas');

        $kelas = DB::table('tblkelas')->get();

        foreach ($kelas as $k) {
            // Skip jika sudah ada
            if (Kelas::where('id_kelas_legacy', $k->idkelas)->exists()) {
                continue;
            }

            try {
                // Cari jurusan berdasarkan idjurusan
                $jurusan = Jurusan::where('program_id', $k->idjurusan)->first();

                // Cari GTK untuk wali kelas dan BK berdasarkan kd_guru
                $waliKelas = $k->idwali ? GTK::where('kd_guru', $k->idwali)->first() : null;
                $bk = $k->idbk ? GTK::where('kd_guru', $k->idbk)->first() : null;

                // Map tingkat dari legacy (X, XI, XII)
                $tingkat = match($k->kelas) {
                    'X' => 'X',
                    'XI' => 'XI',
                    'XII' => 'XII',
                    default => 'X'
                };

                Kelas::create([
                    'id_kelas_legacy' => $k->idkelas,
                    'nama_kelas' => $k->nmkelas,
                    'tingkat' => $tingkat,
                    'jurusan_id' => $jurusan?->id,
                    'wali_kelas_id' => $waliKelas?->id,
                    'bk_id' => $bk?->id,
                    'shift' => $k->shif ?? 'Pagi',
                    'wa_group' => $k->wagroup,
                    'tahun_ajaran' => '2024/2025',
                ]);

                Log::channel('sis')->info('[KelasSeeder] Berhasil import', [
                    'nama_kelas' => $k->nmkelas,
                ]);
            } catch (\Exception $e) {
                Log::channel('sis')->error('[KelasSeeder] Gagal import', [
                    'nama_kelas' => $k->nmkelas,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::channel('sis')->info('[KelasSeeder] Selesai import kelas', [
            'total' => $kelas->count(),
        ]);
    }
}
