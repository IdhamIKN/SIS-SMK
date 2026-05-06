<?php

use App\Models\Sekolah;

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Sekolah SMKN 5 Madiun
    |--------------------------------------------------------------------------
    |
    | Data diambil dari database legacy (tblsekolah, tblsetjam, dll)
    */

    // Data sekolah - akan diload via helper function
    'sekolah' => [
        'nama' => 'SMK NEGERI 5 MADIUN',
        'alamat' => 'Jl. ... Madiun',
        'telp' => '-',
        'email' => '-',
        'kabupaten' => 'Madiun',
        'nama_ks' => '-',
        'nip_ks' => '-',
        'nama_waka' => '-',
        'nip_waka' => '-',
        'wa_sekolah' => '-',
    ],

    // Koordinat dari tblsekolah atau default
    'latitude' => env('SEKOLAH_LATITUDE', -7.6229526),
    'longitude' => env('SEKOLAH_LONGITUDE', 111.5332185),
    'radius_m' => env('SEKOLAH_RADIUS', 50000),

    // Tahun ajaran aktif
    'tahun_ajaran_aktif' => '2026/2027',
    'semester_aktif' => 1,

    // Jam absen default
    'jam_shift' => [
        'pagi' => [
            'masuk' => '07:00:59',
            'limit_masuk' => '07:30:00',
            'pulang' => '15:00:00',
            'limit_pulang' => '20:00:00',
        ],
        'siang' => [
            'masuk' => '12:30:59',
            'limit_masuk' => '13:00:00',
            'pulang' => '16:45:00',
            'limit_pulang' => '20:00:00',
        ],
    ],

    // WA Gateway
    'wa' => [
        'mode' => env('WA_MODE', 'procedure'),
        'gateway_url' => env('WA_GATEWAY_URL'),
        'gateway_token' => env('WA_GATEWAY_TOKEN'),
    ],

    // Threshold alfa default
    'threshold_alfa' => [
        ['jumlah_alfa' => 5, 'tindakan' => 'Panggilan Ortu', 'sanksi' => 'Teguran'],
        ['jumlah_alfa' => 10, 'tindakan' => 'SKors', 'sanksi' => 'Skorsing'],
        ['jumlah_alfa' => 15, 'tindakan' => 'Mutasi', 'sanksi' => 'Mutasi'],
    ],
];
