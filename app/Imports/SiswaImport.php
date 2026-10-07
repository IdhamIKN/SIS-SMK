<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $nisn          = $row['nisn'];
                $namaLengkap   = $row['nama_lengkap'] ?? $row['nama'] ?? null;
                $noHpSiswa     = $row['no_hp_siswa'] ?? $row['hp_siswa'] ?? null;
                $kelas         = Kelas::where('nama_kelas', $row['nama_kelas'] ?? $row['kelas'] ?? null)->first();

                // Buat data siswa
                $siswa = Siswa::create([
                    'nis'           => $row['nis'] ?? null,
                    'nisn'          => $nisn,
                    'nik'           => $row['nik'] ?? null,
                    'nokk'          => $row['nokk'] ?? null,
                    'nama_lengkap'  => $namaLengkap,
                    'jenis_kelamin' => strtoupper($row['jenis_kelamin'] ?? $row['jk'] ?? '') === 'L' ? 'L' : 'P',
                    'kelas_id'      => $kelas?->id,
                    'angkatan'      => $row['angkatan'] ?? null,
                    'tempat_lahir'  => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
                    'alamat'        => $row['alamat'] ?? null,
                    'agama'         => $row['agama'] ?? null,
                    'asal'          => $row['asal'] ?? null,
                    'desa'          => $row['desa'] ?? null,
                    'kelurahan'     => $row['kelurahan'] ?? null,
                    'kecamatan'     => $row['kecamatan'] ?? null,
                    'kabupaten'     => $row['kabupaten'] ?? null,
                    'kode_pos'      => $row['kode_pos'] ?? null,
                    'no_hp_siswa'   => $noHpSiswa,
                    'email'         => $row['email'] ?? null,
                    'no_hp_ortu1'   => $row['no_hp_ortu1'] ?? $row['hp_ortu'] ?? null,
                    'no_hp_ortu2'   => $row['no_hp_ortu2'] ?? null,
                    'nama_ortu1'    => $row['nama_ortu1'] ?? $row['nama_ortu'] ?? null,
                    'nama_ortu2'    => $row['nama_ortu2'] ?? null,
                    'nama_wali'     => $row['nama_wali'] ?? null,
                    'bb'            => $row['bb'] ?? null,
                    'tb'            => $row['tb'] ?? null,
                    'lk'            => $row['lk'] ?? null,
                    'status_aktif'  => true,
                    'noreg_legacy'  => $row['noreg'] ?? null,
                ]);

                // Buat akun user (cek dulu supaya tidak duplikat)
                $email = $nisn . '@smkn5madiun.sch.id';
                $user  = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'       => $namaLengkap,
                        'phone'      => $noHpSiswa,
                        'role_utama' => 'siswa',
                        'password'   => bcrypt($nisn),
                        'siswa_id'   => $siswa->id,
                    ]
                );

                // Assign role spatie
                if (! $user->hasRole('siswa')) {
                    $user->assignRole('siswa');
                }

                // Backfill user_id ke tabel siswas
                $siswa->update(['user_id' => $user->id]);

                Log::channel('sis')->info('[SiswaImport] Berhasil import', [
                    'nisn'    => $nisn,
                    'nama'    => $namaLengkap,
                    'user_id' => $user->id,
                ]);
            } catch (\Exception $e) {
                Log::channel('sis')->error('[SiswaImport] Gagal import', [
                    'row'   => $row->toArray(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Jika dari Excel (angka serial Excel)
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Coba parse berbagai format tanggal
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'm/d/Y', 'd-M-Y'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, trim($value));
            if ($date && $date->format($format) === trim($value)) {
                return $date->format('Y-m-d');
            }
        }

        // Fallback pakai strtotime
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'nisn'          => 'required|string|max:20|unique:siswas,nisn',
            'nama_lengkap'  => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:L,P,Laki-Laki,Perempuan',
            'nama_kelas'    => 'nullable|string|exists:kelas,nama_kelas',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nisn.required'          => 'NISN wajib diisi.',
            'nisn.unique'            => 'NISN sudah terdaftar.',
            'nama_lengkap.required'  => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
            'jenis_kelamin.in'       => 'Jenis kelamin harus L/P atau Laki-Laki/Perempuan.',
            'nama_kelas.exists'      => 'Kelas tidak ditemukan.',
        ];
    }
}
