<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanKehadiranGuru extends Model
{
    use HasFactory;

    protected $table = 'laporan_kehadiran_guru';

    protected $fillable = [
        'jadwal_kbm_id',
        'gtk_id',
        'kelas_id',
        'tanggal',
        'jam_ke',
        'status',
        'dilaporkan_oleh_siswa_id',
        'waktu_laporan',
        'catatan',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_laporan' => 'datetime',
        'jam_ke' => 'integer',
    ];

    public function jadwalKbm(): BelongsTo
    {
        return $this->belongsTo(JadwalKBM::class, 'jadwal_kbm_id');
    }

    public function gtk(): BelongsTo
    {
        return $this->belongsTo(GTK::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function dilaporkanOlehSiswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'dilaporkan_oleh_siswa_id');
    }

    /**
     * Get label status dalam bahasa Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'hijau' => 'Hadir Tepat Waktu',
            'kuning' => 'Hadir Terlambat',
            'merah' => 'Tidak Hadir — No Tugas',
            'abu' => 'Tidak Hadir — Ada Tugas',
            'biru' => 'Hadir Lalu Pergi — Ada Tugas',
            'pink' => 'Hadir Lalu Pergi — No Tugas',
            'orange' => '⚠ Tidak Ada Laporan',
            'putih' => 'Belum Ada Laporan',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get warna status untuk UI
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'hijau' => '#4CAF50',
            'kuning' => '#FFC107',
            'merah' => '#F44336',
            'abu' => '#9E9E9E',
            'biru' => '#2196F3',
            'pink' => '#E91E63',
            'orange' => '#FF5722',
            'putih' => '#FFFFFF',
        ];

        return $colors[$this->status] ?? '#FFFFFF';
    }

    /**
     * Get background fade color untuk status
     */
    public function getStatusBgAttribute(): string
    {
        $bgColors = [
            'hijau' => '#E8F5E8',
            'kuning' => '#FFFDE7',
            'merah' => '#FFEBEE',
            'abu' => '#F5F5F5',
            'biru' => '#E3F2FD',
            'pink' => '#FCE4EC',
            'orange' => '#FFF3E0',
            'putih' => '#FFFFFF',
        ];

        return $bgColors[$this->status] ?? '#FFFFFF';
    }
}
