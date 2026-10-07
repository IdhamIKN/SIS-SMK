<?php

namespace Database\Seeders;

use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mataPelajaran = [
            // Mata Pelajaran Umum
            ['kode_mapel' => 'BI', 'nama_mapel' => 'Bahasa Indonesia', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran bahasa nasional'],
            ['kode_mapel' => 'BING', 'nama_mapel' => 'Bahasa Inggris', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran bahasa internasional'],
            ['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran matematika'],
            ['kode_mapel' => 'PKN', 'nama_mapel' => 'Pendidikan Pancasila dan Kewarganegaraan', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran kewarganegaraan'],
            ['kode_mapel' => 'SEJ', 'nama_mapel' => 'Sejarah', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran sejarah'],
            ['kode_mapel' => 'GEO', 'nama_mapel' => 'Geografi', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran geografi'],
            ['kode_mapel' => 'SOS', 'nama_mapel' => 'Sosiologi', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran sosiologi'],
            ['kode_mapel' => 'EKO', 'nama_mapel' => 'Ekonomi', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran ekonomi'],
            ['kode_mapel' => 'FIS', 'nama_mapel' => 'Fisika', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran fisika'],
            ['kode_mapel' => 'KIM', 'nama_mapel' => 'Kimia', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran kimia'],
            ['kode_mapel' => 'BIO', 'nama_mapel' => 'Biologi', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran biologi'],
            ['kode_mapel' => 'SENBUD', 'nama_mapel' => 'Seni Budaya', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran seni dan budaya'],
            ['kode_mapel' => 'PENJAS', 'nama_mapel' => 'Pendidikan Jasmani dan Olahraga', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran olahraga'],
            ['kode_mapel' => 'TIK', 'nama_mapel' => 'Teknologi Informasi dan Komunikasi', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran teknologi informasi'],
            ['kode_mapel' => 'AGAMA', 'nama_mapel' => 'Pendidikan Agama', 'kategori' => 'umum', 'deskripsi' => 'Mata pelajaran agama sesuai keyakinan'],
            ['kode_mapel' => 'BK', 'nama_mapel' => 'Bimbingan Konseling', 'kategori' => 'umum', 'deskripsi' => 'Layanan bimbingan dan konseling'],

            // Mata Pelajaran Jurusan Teknik Komputer dan Jaringan (TKJ)
            ['kode_mapel' => 'PJOK-TKJ', 'nama_mapel' => 'Produk Kreatif dan Kewirausahaan', 'kategori' => 'jurusan', 'deskripsi' => 'Mata pelajaran kewirausahaan untuk TKJ'],
            ['kode_mapel' => 'SIMDIG', 'nama_mapel' => 'Sistem Digital', 'kategori' => 'jurusan', 'deskripsi' => 'Mata pelajaran dasar sistem digital'],
            ['kode_mapel' => 'RPL', 'nama_mapel' => 'Rekayasa Perangkat Lunak', 'kategori' => 'jurusan', 'deskripsi' => 'Mata pelajaran pemrograman dan software engineering'],
            ['kode_mapel' => 'TKJ', 'nama_mapel' => 'Teknik Komputer dan Jaringan', 'kategori' => 'jurusan', 'deskripsi' => 'Mata pelajaran jaringan komputer'],
            ['kode_mapel' => 'BASDAT', 'nama_mapel' => 'Basis Data', 'kategori' => 'jurusan', 'deskripsi' => 'Mata pelajaran database management'],
            ['kode_mapel' => 'PWEB', 'nama_mapel' => 'Pemrograman Web', 'kategori' => 'jurusan', 'deskripsi' => 'Mata pelajaran pengembangan web'],
            ['kode_mapel' => 'MM', 'nama_mapel' => 'Multimedia', 'kategori' => 'jurusan', 'deskripsi' => 'Mata pelajaran desain multimedia'],

            // Mata Pelajaran Muatan Lokal
            ['kode_mapel' => 'MULOK1', 'nama_mapel' => 'Bahasa Jawa', 'kategori' => 'mulok', 'deskripsi' => 'Muatan lokal bahasa daerah'],
            ['kode_mapel' => 'MULOK2', 'nama_mapel' => 'Kesenian Daerah', 'kategori' => 'mulok', 'deskripsi' => 'Muatan lokal kesenian daerah'],
        ];

        foreach ($mataPelajaran as $mapel) {
            MataPelajaran::create($mapel);
        }
    }
}