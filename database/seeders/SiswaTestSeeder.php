<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SiswaTestSeeder extends Seeder
{
    /**
     * Buat test user siswa untuk uji absensi
     */
    public function run(): void
    {
        // Buat jurusan test
        $jurusan = Jurusan::firstOrCreate(
            ['kode_jurusan' => 'RPL'],
            [
                'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            ]
        );

        // Buat kelas test
        $kelas = Kelas::firstOrCreate(
            ['nama_kelas' => 'XII RPL 1'],
            [
                'tingkat'       => 'XII',
                'jurusan_id'    => $jurusan->id,
                'wali_kelas_id' => null,
                'shift'         => 'Pagi',
                'tahun_ajaran'  => '2024/2025',
            ]
        );

        // Pastikan role siswa ada
        $roleSiswa = Role::firstOrCreate(['name' => 'siswa']);

        // Data siswa test — semua field sesuai $fillable model Siswa
        $siswaData = [
            [
                'nis'           => 'RPL001',
                'nisn'          => '1234567890',
                'nama_lengkap'  => 'Ahmad Fauzi',
                'jenis_kelamin' => 'L',
                'angkatan'      => '2024',
                'tempat_lahir'  => 'Madiun',
                'tanggal_lahir' => '2006-01-15',
                'alamat'        => 'Jl. Test No. 1, Madiun',
                'no_hp_siswa'   => '081234567890',
                'no_hp_ortu1'   => '081234567891',
                'no_hp_ortu2'   => '081234567892',
                'nama_ortu1'    => 'Bapak Fauzi',
                'nama_ortu2'    => 'Ibu Fauzi',
                'nama_wali'     => null,
                'noreg_legacy'  => null,
                'foto'          => null,
                'status_aktif'  => true,
                // untuk user — bukan kolom siswas
                'email'         => 'ahmad.fauzi@test.com',
            ],
            [
                'nis'           => 'RPL002',
                'nisn'          => '1234567891',
                'nama_lengkap'  => 'Siti Aminah',
                'jenis_kelamin' => 'P',
                'angkatan'      => '2024',
                'tempat_lahir'  => 'Madiun',
                'tanggal_lahir' => '2006-03-20',
                'alamat'        => 'Jl. Test No. 2, Madiun',
                'no_hp_siswa'   => '081234567893',
                'no_hp_ortu1'   => '081234567894',
                'no_hp_ortu2'   => '081234567895',
                'nama_ortu1'    => 'Ibu Aminah',
                'nama_ortu2'    => 'Bapak Aminah',
                'nama_wali'     => null,
                'noreg_legacy'  => null,
                'foto'          => null,
                'status_aktif'  => true,
                'email'         => 'siti.aminah@test.com',
            ],
            [
                'nis'           => 'RPL003',
                'nisn'          => '1234567892',
                'nama_lengkap'  => 'Budi Santoso',
                'jenis_kelamin' => 'L',
                'angkatan'      => '2024',
                'tempat_lahir'  => 'Madiun',
                'tanggal_lahir' => '2006-05-10',
                'alamat'        => 'Jl. Test No. 3, Madiun',
                'no_hp_siswa'   => '081234567896',
                'no_hp_ortu1'   => '081234567897',
                'no_hp_ortu2'   => '081234567898',
                'nama_ortu1'    => 'Bapak Santoso',
                'nama_ortu2'    => 'Ibu Santoso',
                'nama_wali'     => null,
                'noreg_legacy'  => null,
                'foto'          => null,
                'status_aktif'  => true,
                'email'         => 'budi.santoso@test.com',
            ],
        ];

        foreach ($siswaData as $data) {
            // Buat user untuk siswa
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'       => $data['nama_lengkap'],
                    'password'   => Hash::make('password'),
                    'avatar'     => null,
                    'role_utama' => 'siswa',
                ]
            );

            // Assign role siswa
            if (!$user->hasRole('siswa')) {
                $user->assignRole($roleSiswa);
            }

            // Pisahkan field siswa (exclude email yang bukan kolom siswas)
            $siswaFields = array_diff_key($data, array_flip(['email']));

            // Buat record siswa
            $siswa = Siswa::firstOrCreate(
                ['nisn' => $data['nisn']],
                array_merge($siswaFields, [
                    'user_id'  => $user->id,
                    'kelas_id' => $kelas->id,
                ])
            );

            // Update user dengan siswa_id
            $user->update(['siswa_id' => $siswa->id]);
        }

        $this->command->info('✅ Test siswa berhasil dibuat!');
        $this->command->info('📧 ahmad.fauzi@test.com | siti.aminah@test.com | budi.santoso@test.com');
        $this->command->info('🔑 Password: password');
    }
}