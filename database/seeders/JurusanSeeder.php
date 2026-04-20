<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        Log::channel('sis')->info('[JurusanSeeder] Mulai import jurusan');

        $jurusans = [
            ['kode_jurusan' => 'UMUM', 'nama_jurusan' => 'UMUM JURUSAN', 'program_id' => 1],
            ['kode_jurusan' => 'TOT', 'nama_jurusan' => 'TEKNIK OTOTRONIK', 'program_id' => 2],
            ['kode_jurusan' => 'TSM', 'nama_jurusan' => 'TEKNIK SEPEDA MOTOR', 'program_id' => 3],
            ['kode_jurusan' => 'TKR', 'nama_jurusan' => 'TEKNIK KENDARAAN RINGAN', 'program_id' => 4],
            ['kode_jurusan' => 'AK', 'nama_jurusan' => 'AKUNTANSI', 'program_id' => 5],
            ['kode_jurusan' => 'MOP', 'nama_jurusan' => 'MANAJEMEN PERKANTORAN', 'program_id' => 6],
            ['kode_jurusan' => 'TOI', 'nama_jurusan' => 'TEKNIK OTOMASI INDUSTRI', 'program_id' => 7],
            ['kode_jurusan' => 'TAB', 'nama_jurusan' => 'TEKNIK ALAT BERAT', 'program_id' => 8],
            ['kode_jurusan' => 'TPL', 'nama_jurusan' => 'TEKNIK PENGELASAN', 'program_id' => 9],
            ['kode_jurusan' => 'AKL', 'nama_jurusan' => 'AKUNTANSI KEUANGAN LEMBAGA', 'program_id' => 10],
            ['kode_jurusan' => 'TBSM', 'nama_jurusan' => 'TEKNIK DAN BISNIS SEPEDA MOTOR', 'program_id' => 11],
            ['kode_jurusan' => 'TKRO', 'nama_jurusan' => 'TEKNIK KENDARAAN RINGAN OTOMOTIF', 'program_id' => 12],
            ['kode_jurusan' => 'OTKP', 'nama_jurusan' => 'OTOMATISASI TATA KELOLA PERKANTORAN', 'program_id' => 13],
        ];

        foreach ($jurusans as $data) {
            Jurusan::create($data);
        }

        Log::channel('sis')->info('[JurusanSeeder] Selesai import jurusan', [
            'total' => count($jurusans),
        ]);
    }
}
