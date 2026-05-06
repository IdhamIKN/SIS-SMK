<?php

namespace Database\Seeders;

use App\Models\GTK;
use App\Models\JadwalKBM;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\LaporanKehadiranGuru;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GTKSeeder extends Seeder
{
    public function run(): void
    {
        // Create GTK user
        $gtkUser = User::firstOrCreate([
            'email' => 'guru@test.com',
        ], [
            'name' => 'Guru Test',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'role_utama' => 'gtk',
        ]);

        $gtkUser->assignRole('gtk');

        // Create GTK record
        $gtk = GTK::firstOrCreate([
            'kd_guru' => 'GTK001',
        ], [
            'user_id' => $gtkUser->id,
            'nama_lengkap' => 'Guru Test',
            'jenis_kelamin' => 'L',
            'jabatan' => 'Guru Mata Pelajaran',
            'mata_pelajaran' => 'Matematika',
            'status_aktif' => true,
        ]);

        // Create Jurusan
        $jurusan = Jurusan::firstOrCreate([
            'kode_jurusan' => 'MM',
            'nama_jurusan' => 'Multimedia',
        ]);

        // Create Kelas
        $kelas = Kelas::firstOrCreate([
            'nama_kelas' => 'X MM 1',
            'tingkat' => 'X',
            'jurusan_id' => $jurusan->id,
            'wali_kelas_id' => $gtk->id,
            'shift' => 'Pagi',
            'tahun_ajaran' => '2024/2025',
        ]);

        // Create Jadwal KBM
        $jadwal = JadwalKBM::create([
            'kelas_id' => $kelas->id,
            'gtk_id' => $gtk->id,
            'hari' => 'Senin',
            'jam_ke' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:45:00',
            'mata_pelajaran' => 'Matematika',
            'tahun_ajaran' => '2024/2025',
            'semester' => '1',
        ]);

        // Create Laporan Kehadiran
        LaporanKehadiranGuru::create([
            'jadwal_kbm_id' => $jadwal->id,
            'gtk_id' => $gtk->id,
            'kelas_id' => $kelas->id,
            'tanggal' => now()->toDateString(),
            'jam_ke' => 1,
            'status' => 'hijau',
            'waktu_laporan' => now(),
        ]);

        $this->command->info('GTK seeder completed successfully!');
    }
}
