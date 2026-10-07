<?php

namespace Database\Seeders;

use App\Models\GTK;
use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;

class GtkMataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gtks = GTK::all();
        $mataPelajaran = MataPelajaran::all()->keyBy('nama_mapel');

        foreach ($gtks as $gtk) {
            if ($gtk->mata_pelajaran) {
                // Split kompetensi berdasarkan koma dan spasi
                $kompetensiList = array_map('trim', explode(',', $gtk->mata_pelajaran));

                foreach ($kompetensiList as $kompetensi) {
                    // Cari mata pelajaran yang cocok
                    $mapel = $this->findMatchingMataPelajaran($kompetensi, $mataPelajaran);

                    if ($mapel) {
                        // Attach jika belum ada
                        if (!$gtk->mataPelajaran()->where('mata_pelajaran_id', $mapel->id)->exists()) {
                            $gtk->mataPelajaran()->attach($mapel->id);
                        }
                    }
                }
            }
        }
    }

    /**
     * Cari mata pelajaran yang cocok berdasarkan kompetensi
     */
    private function findMatchingMataPelajaran(string $kompetensi, $mataPelajaranCollection)
    {
        $kompetensi = strtolower(trim($kompetensi));

        // Mapping kompetensi ke nama mata pelajaran
        $mapping = [
            'bahasa indonesia' => 'Bahasa Indonesia',
            'bahasa inggris' => 'Bahasa Inggris',
            'matematika' => 'Matematika',
            'fisika' => 'Fisika',
            'kimia' => 'Kimia',
            'biologi' => 'Biologi',
            'sejarah' => 'Sejarah',
            'geografi' => 'Geografi',
            'sosiologi' => 'Sosiologi',
            'ekonomi' => 'Ekonomi',
            'seni budaya' => 'Seni Budaya',
            'penjas' => 'Pendidikan Jasmani dan Olahraga',
            'tik' => 'Teknologi Informasi dan Komunikasi',
            'agama' => 'Pendidikan Agama',
            'bk' => 'Bimbingan Konseling',
            'pjok' => 'Pendidikan Jasmani dan Olahraga',
            'produktif' => 'Produk Kreatif dan Kewirausahaan',
            'sistem digital' => 'Sistem Digital',
            'rpl' => 'Rekayasa Perangkat Lunak',
            'tkj' => 'Teknik Komputer dan Jaringan',
            'basis data' => 'Basis Data',
            'pemrograman web' => 'Pemrograman Web',
            'multimedia' => 'Multimedia',
            'bahasa jawa' => 'Bahasa Jawa',
            'kesenian daerah' => 'Kesenian Daerah',
        ];

        // Cek mapping langsung
        if (isset($mapping[$kompetensi])) {
            return $mataPelajaranCollection->get($mapping[$kompetensi]);
        }

        // Cek partial matching
        foreach ($mataPelajaranCollection as $mapel) {
            if (str_contains(strtolower($mapel->nama_mapel), $kompetensi) ||
                str_contains($kompetensi, strtolower($mapel->nama_mapel))) {
                return $mapel;
            }
        }

        return null;
    }
}