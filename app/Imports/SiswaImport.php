<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
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
                // Cari kelas berdasarkan nama_kelas
                $kelas = Kelas::where('nama_kelas', $row['nama_kelas'] ?? $row['kelas'])->first();

                Siswa::create([
                    'nis' => $row['nis'] ?? null,
                    'nisn' => $row['nisn'],
                    'nama_lengkap' => $row['nama_lengkap'] ?? $row['nama'],
                    'jenis_kelamin' => strtoupper($row['jenis_kelamin'] ?? $row['jk']) === 'L' ? 'L' : 'P',
                    'kelas_id' => $kelas?->id,
                    'angkatan' => $row['angkatan'] ?? null,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
                    'no_hp_siswa' => $row['no_hp_siswa'] ?? $row['hp_siswa'] ?? null,
                    'no_hp_ortu1' => $row['no_hp_ortu1'] ?? $row['hp_ortu'] ?? null,
                    'no_hp_ortu2' => $row['no_hp_ortu2'] ?? null,
                    'nama_ortu1' => $row['nama_ortu1'] ?? $row['nama_ortu'] ?? null,
                    'nama_ortu2' => $row['nama_ortu2'] ?? null,
                    'nama_wali' => $row['nama_wali'] ?? null,
                    'status_aktif' => true,
                    'noreg_legacy' => $row['noreg'] ?? null,
                ]);

                Log::channel('sis')->info('[SiswaImport] Berhasil import', [
                    'nisn' => $row['nisn'],
                    'nama' => $row['nama_lengkap'] ?? $row['nama'],
                ]);
            } catch (\Exception $e) {
                Log::channel('sis')->error('[SiswaImport] Gagal import', [
                    'row' => $row->toArray(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'nisn' => 'required|string|max:20|unique:siswas,nisn',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string|in:L,P,Laki-Laki,Perempuan',
            'nama_kelas' => 'nullable|string|exists:kelas,nama_kelas',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L/P atau Laki-Laki/Perempuan.',
            'nama_kelas.exists' => 'Kelas tidak ditemukan.',
        ];
    }
}
