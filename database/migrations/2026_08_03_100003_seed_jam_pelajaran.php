<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seed/update tblsetjam sesuai jadwal resmi sekolah (gambar).
 *
 * kelompok_jam:
 *   'reguler'      → Senin–Kamis, Kelas 10    (id 1–12)
 *   'reguler_1112' → Senin–Kamis, Kelas 11-12 (id 13–24)
 *   'jumat'        → Jumat semua kelas         (id 31–40)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Hapus lama id 1-24 dan 31-40 agar bisa insert ulang bersih
        DB::table('tblsetjam')
            ->where(function ($q) {
                $q->whereBetween('id_jam', [1, 24])
                  ->orWhereBetween('id_jam', [31, 40]);
            })
            ->delete();

        $rows = [
            // ── Reguler Kelas 10 (Senin–Kamis) ─────────────────────────────
            [1,  'Pagi',  'reguler',      'Jam Ke-1',            '07:00:00', '07:30:00'],
            [2,  'Pagi',  'reguler',      'Jam Ke-2',            '07:30:00', '08:00:00'],
            [3,  'Pagi',  'reguler',      'Jam Ke-3',            '08:00:00', '08:45:00'],
            [4,  'Pagi',  'reguler',      'Jam Ke-4',            '08:45:00', '09:30:00'],
            [5,  'Pagi',  'reguler',      'Istirahat 1',         '09:30:00', '09:45:00'],
            [6,  'Pagi',  'reguler',      'Jam Ke-5',            '09:45:00', '10:30:00'],
            [7,  'Siang', 'reguler',      'Jam Ke-6',            '10:30:00', '11:15:00'],
            [8,  'Siang', 'reguler',      'Jam Ke-7',            '11:15:00', '12:00:00'],
            [9,  'Siang', 'reguler',      'Istirahat 2',         '12:00:00', '12:30:00'],
            [10, 'Siang', 'reguler',      'Jam Ke-8',            '12:30:00', '13:15:00'],
            [11, 'Siang', 'reguler',      'Jam Ke-9',            '13:15:00', '14:00:00'],
            [12, 'Siang', 'reguler',      'Jam Ke-10',           '14:00:00', '14:45:00'],

            // ── Reguler Kelas 11-12 (Senin–Kamis) ──────────────────────────
            [13, 'Pagi',  'reguler_1112', 'Jam Ke-1 (11-12)',    '07:00:00', '07:30:00'],
            [14, 'Pagi',  'reguler_1112', 'Jam Ke-2 (11-12)',    '07:30:00', '08:00:00'],
            [15, 'Pagi',  'reguler_1112', 'Jam Ke-3 (11-12)',    '08:00:00', '08:45:00'],
            [16, 'Pagi',  'reguler_1112', 'Jam Ke-4 (11-12)',    '08:45:00', '09:30:00'],
            [17, 'Pagi',  'reguler_1112', 'Istirahat 1 (11-12)', '09:30:00', '09:45:00'],
            [18, 'Pagi',  'reguler_1112', 'Jam Ke-5 (11-12)',    '09:45:00', '10:30:00'],
            [19, 'Siang', 'reguler_1112', 'Jam Ke-6 (11-12)',    '10:30:00', '11:15:00'],
            [20, 'Siang', 'reguler_1112', 'Jam Ke-7 (11-12)',    '11:15:00', '12:00:00'],
            [21, 'Siang', 'reguler_1112', 'Jam Ke-8 (11-12)',    '12:00:00', '12:45:00'],
            [22, 'Siang', 'reguler_1112', 'Istirahat 2 (11-12)', '12:45:00', '13:15:00'],
            [23, 'Siang', 'reguler_1112', 'Jam Ke-9 (11-12)',    '13:15:00', '14:00:00'],
            [24, 'Siang', 'reguler_1112', 'Jam Ke-10 (11-12)',   '14:00:00', '14:45:00'],

            // ── Jumat – semua kelas ─────────────────────────────────────────
            [31, 'Pagi',  'jumat',        'Jam Ke-1 (Jumat)',    '07:00:00', '07:30:00'],
            [32, 'Pagi',  'jumat',        'Jam Ke-2 (Jumat)',    '07:30:00', '08:00:00'],
            [33, 'Pagi',  'jumat',        'Jam Ke-3 (Jumat)',    '08:00:00', '08:40:00'],
            [34, 'Pagi',  'jumat',        'Jam Ke-4 (Jumat)',    '08:40:00', '09:20:00'],
            [35, 'Pagi',  'jumat',        'Istirahat (Jumat)',   '09:20:00', '09:40:00'],
            [36, 'Pagi',  'jumat',        'Jam Ke-5 (Jumat)',    '09:40:00', '10:20:00'],
            [37, 'Pagi',  'jumat',        'Jam Ke-6 (Jumat)',    '10:20:00', '11:00:00'],
            [38, 'Siang', 'jumat',        'Ekskul 1 (Jumat)',    '13:00:00', '13:45:00'],
            [39, 'Siang', 'jumat',        'Ekskul 2 (Jumat)',    '13:45:00', '14:30:00'],
            [40, 'Siang', 'jumat',        'Ekskul 3 (Jumat)',    '14:30:00', '15:15:00'],
        ];

        foreach ($rows as [$id, $shif, $klp, $nama, $tin, $tout]) {
            DB::table('tblsetjam')->insert([
                'id_jam'       => $id,
                'shif'         => $shif,
                'kelompok_jam' => $klp,
                'nama_jam'     => $nama,
                'time_in'      => $tin,
                'limit_in'     => null,
                'time_out'     => $tout,
                'limit_out'    => null,
                'statusjam'    => 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('tblsetjam')
            ->where(function ($q) {
                $q->whereBetween('id_jam', [1, 24])
                  ->orWhereBetween('id_jam', [31, 40]);
            })
            ->delete();

        // Restore 10 jam original
        $original = [
            [1,  'Pagi',  'Jam Ke-1',  '07:00:00', '07:30:00'],
            [2,  'Pagi',  'jam Ke-2',  '07:30:00', '08:00:00'],
            [3,  'Pagi',  'Jam Ke-3',  '08:00:00', '08:45:00'],
            [4,  'Pagi',  'jam Ke-4',  '08:45:00', '09:30:00'],
            [5,  'Pagi',  'Jam Ke-5',  '09:45:00', '10:30:00'],
            [6,  'Siang', 'Jam Ke-6',  '10:30:00', '11:15:00'],
            [7,  'Siang', 'Jam Ke-7',  '11:15:00', '12:00:00'],
            [8,  'Siang', 'Jam Ke-8',  '12:30:00', '13:15:00'],
            [9,  'Siang', 'jam Ke-9',  '13:15:00', '14:00:00'],
            [10, 'Siang', 'jam Ke-10', '14:00:00', '14:45:00'],
        ];

        foreach ($original as [$id, $shif, $nama, $tin, $tout]) {
            DB::table('tblsetjam')->insert([
                'id_jam' => $id, 'shif' => $shif, 'kelompok_jam' => 'reguler',
                'nama_jam' => $nama, 'time_in' => $tin, 'limit_in' => null,
                'time_out' => $tout, 'limit_out' => null, 'statusjam' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
};
