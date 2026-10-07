<?php

/**
 * Konfigurasi warna dan label status kehadiran guru.
 *
 * SINGLE SOURCE OF TRUTH — dipakai oleh:
 *   - Model LaporanKehadiranGuru (accessor)
 *   - Blade views (via config('status_guru.statuses'))
 *   - JS melalui @json(config('status_guru.statuses')) di view
 *
 * Palet warna: Tailwind CSS — konsisten antara panel/realtime dan dashboard-laporan.
 */

return [

    /*
    |------------------------------------------------------------------
    | Definisi per-status
    |------------------------------------------------------------------
    | Setiap status memiliki:
    |   color   — hex warna solid (dot/ikon)
    |   bg      — hex warna background (badge/chip)
    |   text    — hex warna teks (untuk badge)
    |   label   — teks singkat untuk badge/chip
    |   desc    — deskripsi lengkap untuk form/pilihan
    */
    'statuses' => [
        'hijau' => [
            'color' => '#22c55e',
            'bg'    => '#dcfce7',
            'text'  => '#15803d',
            'label' => 'Hadir Tepat Waktu',
            'desc'  => 'Guru hadir sesuai jadwal (≤ 10 menit setelah bel).',
        ],
        'kuning' => [
            'color' => '#eab308',
            'bg'    => '#fef9c3',
            'text'  => '#a16207',
            'label' => 'Hadir Terlambat',
            'desc'  => 'Guru hadir melewati jam mulai KBM (> 10 menit).',
        ],
        'merah' => [
            'color' => '#ef4444',
            'bg'    => '#fee2e2',
            'text'  => '#b91c1c',
            'label' => 'Tidak Hadir',
            'desc'  => 'Guru tidak hadir dan tidak memberikan tugas.',
        ],
        'abu' => [
            'color' => '#64748b',
            'bg'    => '#f1f5f9',
            'text'  => '#475569',
            'label' => 'Tidak Hadir + Ada Tugas',
            'desc'  => 'Guru tidak hadir tetapi memberikan tugas kepada siswa.',
        ],
        'biru' => [
            'color' => '#3b82f6',
            'bg'    => '#dbeafe',
            'text'  => '#1d4ed8',
            'label' => 'Pergi + Ada Tugas',
            'desc'  => 'Guru hadir lalu meninggalkan kelas dengan meninggalkan tugas.',
        ],
        'pink' => [
            'color' => '#ec4899',
            'bg'    => '#fce7f3',
            'text'  => '#be185d',
            'label' => 'Pergi + No Tugas',
            'desc'  => 'Guru hadir lalu meninggalkan kelas tanpa meninggalkan tugas.',
        ],
        'orange' => [
            'color' => '#f97316',
            'bg'    => '#ffedd5',
            'text'  => '#c2410c',
            'label' => 'Tanpa Laporan',
            'desc'  => 'Tidak ada laporan yang masuk untuk sesi ini.',
        ],
        'putih' => [
            'color' => '#94a3b8',
            'bg'    => '#f8fafc',
            'text'  => '#64748b',
            'label' => 'Belum Lapor',
            'desc'  => 'Jam pelajaran belum selesai atau belum dilaporkan.',
        ],
    ],

    /*
    |------------------------------------------------------------------
    | Status yang dianggap "bermasalah" untuk filter/highlight
    |------------------------------------------------------------------
    */
    'problematic' => ['merah', 'kuning', 'orange'],

    /*
    |------------------------------------------------------------------
    | Urutan tampil di legend/chart
    |------------------------------------------------------------------
    */
    'order' => ['hijau', 'kuning', 'merah', 'abu', 'biru', 'pink', 'orange', 'putih'],

];
