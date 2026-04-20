<?php

namespace Database\Seeders;

use App\Models\GTK;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GTKSeeder extends Seeder
{
    public function run(): void
    {
        Log::channel('sis')->info('[GTKSeeder] Mulai import GTK dari tblpegawai');

        $pegawais = DB::table('tblpegawai')->get();

        foreach ($pegawais as $pegawai) {
            // Skip jika sudah ada
            if (GTK::where('kd_guru', $pegawai->kdguru)->exists()) {
                continue;
            }

            try {
                GTK::create([
                    'kd_guru' => $pegawai->kdguru,
                    'nama_lengkap' => $pegawai->nmpegawai,
                    'jenis_kelamin' => 'L', // Default, bisa disesuaikan
                    'no_hp' => $pegawai->telp ?? null,
                    'jabatan' => $pegawai->jabatan,
                    'status_aktif' => $pegawai->status == 'AKTIF',
                    'acc_absen' => $pegawai->accabsen == 'YA',
                    'acc_kurikulum' => $pegawai->acckurikulum == 'YA',
                    'acc_jurnal' => $pegawai->accjurnal == 'YA',
                    'acc_bk' => $pegawai->accbk == 'YA',
                    'guru_piket' => $pegawai->gurupiket == 'YA',
                    'acc_profil' => $pegawai->accprofil == 'YA',
                    'group_acc' => $pegawai->groupacc == 'YA',
                    'view_siswa' => $pegawai->viewsiswa ?? 'limit',
                ]);

                Log::channel('sis')->info('[GTKSeeder] Berhasil import', [
                    'kd_guru' => $pegawai->kdguru,
                    'nama' => $pegawai->nmpegawai,
                ]);
            } catch (\Exception $e) {
                Log::channel('sis')->error('[GTKSeeder] Gagal import', [
                    'kd_guru' => $pegawai->kdguru,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::channel('sis')->info('[GTKSeeder] Selesai import GTK', [
            'total' => $pegawais->count(),
        ]);
    }
}
