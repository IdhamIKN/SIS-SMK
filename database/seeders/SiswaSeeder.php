<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::channel('sis')->info('[SiswaSeeder] Mulai import siswa dari tblsiswa');

        $siswas = DB::table('tblsiswa')->get();

        foreach ($siswas as $siswa) {
            // Skip jika sudah ada
            if (Siswa::where('noreg_legacy', $siswa->noreg)->exists()) {
                continue;
            }

            try {
                // Cari kelas berdasarkan nama_kelas
                $kelas = Kelas::where('nama_kelas', $siswa->nmkelas)->first();

                Siswa::create([
                    'nisn' => $siswa->nisn,
                    'nama_lengkap' => $siswa->nama,
                    'jenis_kelamin' => $siswa->gender == 'Laki-Laki' ? 'L' : 'P',
                    'kelas_id' => $kelas?->id,
                    'angkatan' => ($siswa->thnpelajaran && strpos($siswa->thnpelajaran, '/') !== false) ? explode('/', $siswa->thnpelajaran)[0] : null,
                    'foto' => $siswa->photo ?? null,
                    'no_hp_siswa' => $siswa->hpsiswa ?? null,
                    'no_hp_ortu1' => $siswa->hportu ?? null,
                    'status_aktif' => $siswa->statusx === 'AKTIF',
                    'noreg_legacy' => $siswa->noreg,
                ]);

                Log::channel('sis')->info('[SiswaSeeder] Berhasil import', [
                    'noreg' => $siswa->noreg,
                    'nama' => $siswa->nama,
                ]);
            } catch (\Exception $e) {
                Log::channel('sis')->error('[SiswaSeeder] Gagal import', [
                    'noreg' => $siswa->noreg,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::channel('sis')->info('[SiswaSeeder] Selesai import siswa', [
            'total' => $siswas->count(),
        ]);
    }
}
