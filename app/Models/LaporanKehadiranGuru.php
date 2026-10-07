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
        'wa_terkirim',
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
        return config("status_guru.statuses.{$this->status}.label", ucfirst($this->status));
    }

    /**
     * Get warna status untuk UI (Tailwind palette — konsisten dengan panel & dashboard)
     */
    public function getStatusColorAttribute(): string
    {
        return config("status_guru.statuses.{$this->status}.color", '#94a3b8');
    }

    /**
     * Get background fade color untuk status
     */
    public function getStatusBgAttribute(): string
    {
        return config("status_guru.statuses.{$this->status}.bg", '#f8fafc');
    }

    /**
     * Get warna teks status untuk badge
     */
    public function getStatusTextAttribute(): string
    {
        return config("status_guru.statuses.{$this->status}.text", '#64748b');
    }
}
