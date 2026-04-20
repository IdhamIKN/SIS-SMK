<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SiswaExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Siswa::with(['kelas']);
    }

    public function headings(): array
    {
        return [
            'NIS',
            'NISN',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Kelas',
            'Angkatan',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'No HP Siswa',
            'No HP Ortu 1',
            'No HP Ortu 2',
            'Nama Ortu 1',
            'Nama Ortu 2',
            'Nama Wali',
            'Status Aktif',
        ];
    }

    public function map($siswa): array
    {
        return [
            $siswa->nis,
            $siswa->nisn,
            $siswa->nama_lengkap,
            $siswa->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan',
            $siswa->kelas?->nama_kelas,
            $siswa->angkatan,
            $siswa->tempat_lahir,
            $siswa->tanggal_lahir?->format('d/m/Y'),
            $siswa->alamat,
            $siswa->no_hp_siswa,
            $siswa->no_hp_ortu1,
            $siswa->no_hp_ortu2,
            $siswa->nama_ortu1,
            $siswa->nama_ortu2,
            $siswa->nama_wali,
            $siswa->status_aktif ? 'Aktif' : 'Non Aktif',
        ];
    }
}
