<?php

namespace Database\Seeders;

use App\Models\SetJam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetJamSeeder extends Seeder
{
    /**
     * Seeder ini sudah digantikan oleh migration
     * 2026_08_03_100003_seed_jam_pelajaran.php
     *
     * Dipertahankan hanya untuk keperluan fresh install / testing.
     * Data aktual sudah sesuai gambar jadwal resmi sekolah.
     */
    public function run(): void
    {
        // Hapus semua data lama terlebih dahulu
        DB::table('tblsetjam')->truncate();

        $rows = [
            // ── Reguler Kelas 10 (Senin–Kamis) ─────────────────────────────
            ['id_jam' => 1,  'shif' => 'Pagi',  'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-1',            'time_in' => '07:00:00', 'limit_in' => null, 'time_out' => '07:30:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 2,  'shif' => 'Pagi',  'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-2',            'time_in' => '07:30:00', 'limit_in' => null, 'time_out' => '08:00:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 3,  'shif' => 'Pagi',  'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-3',            'time_in' => '08:00:00', 'limit_in' => null, 'time_out' => '08:45:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 4,  'shif' => 'Pagi',  'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-4',            'time_in' => '08:45:00', 'limit_in' => null, 'time_out' => '09:30:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 5,  'shif' => 'Pagi',  'kelompok_jam' => 'reguler',      'nama_jam' => 'Istirahat 1',         'time_in' => '09:30:00', 'limit_in' => null, 'time_out' => '09:45:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 6,  'shif' => 'Pagi',  'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-5',            'time_in' => '09:45:00', 'limit_in' => null, 'time_out' => '10:30:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 7,  'shif' => 'Siang', 'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-6',            'time_in' => '10:30:00', 'limit_in' => null, 'time_out' => '11:15:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 8,  'shif' => 'Siang', 'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-7',            'time_in' => '11:15:00', 'limit_in' => null, 'time_out' => '12:00:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 9,  'shif' => 'Siang', 'kelompok_jam' => 'reguler',      'nama_jam' => 'Istirahat 2',         'time_in' => '12:00:00', 'limit_in' => null, 'time_out' => '12:30:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 10, 'shif' => 'Siang', 'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-8',            'time_in' => '12:30:00', 'limit_in' => null, 'time_out' => '13:15:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 11, 'shif' => 'Siang', 'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-9',            'time_in' => '13:15:00', 'limit_in' => null, 'time_out' => '14:00:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 12, 'shif' => 'Siang', 'kelompok_jam' => 'reguler',      'nama_jam' => 'Jam Ke-10',           'time_in' => '14:00:00', 'limit_in' => null, 'time_out' => '14:45:00', 'limit_out' => null, 'statusjam' => 1],

            // ── Reguler Kelas 11-12 (Senin–Kamis) ──────────────────────────
            ['id_jam' => 13, 'shif' => 'Pagi',  'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-1 (11-12)',    'time_in' => '07:00:00', 'limit_in' => null, 'time_out' => '07:30:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 14, 'shif' => 'Pagi',  'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-2 (11-12)',    'time_in' => '07:30:00', 'limit_in' => null, 'time_out' => '08:00:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 15, 'shif' => 'Pagi',  'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-3 (11-12)',    'time_in' => '08:00:00', 'limit_in' => null, 'time_out' => '08:45:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 16, 'shif' => 'Pagi',  'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-4 (11-12)',    'time_in' => '08:45:00', 'limit_in' => null, 'time_out' => '09:30:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 17, 'shif' => 'Pagi',  'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Istirahat 1 (11-12)', 'time_in' => '09:30:00', 'limit_in' => null, 'time_out' => '09:45:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 18, 'shif' => 'Pagi',  'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-5 (11-12)',    'time_in' => '09:45:00', 'limit_in' => null, 'time_out' => '10:30:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 19, 'shif' => 'Siang', 'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-6 (11-12)',    'time_in' => '10:30:00', 'limit_in' => null, 'time_out' => '11:15:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 20, 'shif' => 'Siang', 'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-7 (11-12)',    'time_in' => '11:15:00', 'limit_in' => null, 'time_out' => '12:00:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 21, 'shif' => 'Siang', 'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-8 (11-12)',    'time_in' => '12:00:00', 'limit_in' => null, 'time_out' => '12:45:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 22, 'shif' => 'Siang', 'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Istirahat 2 (11-12)', 'time_in' => '12:45:00', 'limit_in' => null, 'time_out' => '13:15:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 23, 'shif' => 'Siang', 'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-9 (11-12)',    'time_in' => '13:15:00', 'limit_in' => null, 'time_out' => '14:00:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 24, 'shif' => 'Siang', 'kelompok_jam' => 'reguler_1112', 'nama_jam' => 'Jam Ke-10 (11-12)',   'time_in' => '14:00:00', 'limit_in' => null, 'time_out' => '14:45:00', 'limit_out' => null, 'statusjam' => 1],

            // ── Jumat – Semua Kelas ─────────────────────────────────────────
            ['id_jam' => 31, 'shif' => 'Pagi',  'kelompok_jam' => 'jumat',        'nama_jam' => 'Jam Ke-1 (Jumat)',    'time_in' => '07:00:00', 'limit_in' => null, 'time_out' => '07:30:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 32, 'shif' => 'Pagi',  'kelompok_jam' => 'jumat',        'nama_jam' => 'Jam Ke-2 (Jumat)',    'time_in' => '07:30:00', 'limit_in' => null, 'time_out' => '08:00:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 33, 'shif' => 'Pagi',  'kelompok_jam' => 'jumat',        'nama_jam' => 'Jam Ke-3 (Jumat)',    'time_in' => '08:00:00', 'limit_in' => null, 'time_out' => '08:40:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 34, 'shif' => 'Pagi',  'kelompok_jam' => 'jumat',        'nama_jam' => 'Jam Ke-4 (Jumat)',    'time_in' => '08:40:00', 'limit_in' => null, 'time_out' => '09:20:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 35, 'shif' => 'Pagi',  'kelompok_jam' => 'jumat',        'nama_jam' => 'Istirahat (Jumat)',   'time_in' => '09:20:00', 'limit_in' => null, 'time_out' => '09:40:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 36, 'shif' => 'Pagi',  'kelompok_jam' => 'jumat',        'nama_jam' => 'Jam Ke-5 (Jumat)',    'time_in' => '09:40:00', 'limit_in' => null, 'time_out' => '10:20:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 37, 'shif' => 'Pagi',  'kelompok_jam' => 'jumat',        'nama_jam' => 'Jam Ke-6 (Jumat)',    'time_in' => '10:20:00', 'limit_in' => null, 'time_out' => '11:00:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 38, 'shif' => 'Siang', 'kelompok_jam' => 'jumat',        'nama_jam' => 'Ekskul 1 (Jumat)',    'time_in' => '13:00:00', 'limit_in' => null, 'time_out' => '13:45:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 39, 'shif' => 'Siang', 'kelompok_jam' => 'jumat',        'nama_jam' => 'Ekskul 2 (Jumat)',    'time_in' => '13:45:00', 'limit_in' => null, 'time_out' => '14:30:00', 'limit_out' => null, 'statusjam' => 1],
            ['id_jam' => 40, 'shif' => 'Siang', 'kelompok_jam' => 'jumat',        'nama_jam' => 'Ekskul 3 (Jumat)',    'time_in' => '14:30:00', 'limit_in' => null, 'time_out' => '15:15:00', 'limit_out' => null, 'statusjam' => 1],
        ];

        foreach ($rows as $row) {
            SetJam::create($row);
        }
    }
}
