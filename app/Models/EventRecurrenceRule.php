<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRecurrenceRule extends Model
{
    use HasFactory;

    public const FREQUENCIES = ['daily', 'weekly', 'monthly', 'yearly', 'custom'];

    public const DAY_LABELS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    protected $fillable = [
        'event_id',
        'frequency',
        'interval',
        'days_of_week',
        'repeat_until',
        'occurrence_count',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'repeat_until' => 'date',
        'interval' => 'integer',
        'occurrence_count' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function label(): string
    {
        $interval = max(1, (int) $this->interval);

        return match ($this->frequency) {
            'daily' => $interval > 1 ? "Setiap {$interval} hari" : 'Harian',
            'weekly' => $this->weeklyLabel($interval),
            'monthly' => $interval > 1 ? "Setiap {$interval} bulan" : 'Bulanan',
            'yearly' => $interval > 1 ? "Setiap {$interval} tahun" : 'Tahunan',
            'custom' => "Setiap {$interval} hari",
            default => 'Tidak berulang',
        };
    }

    private function weeklyLabel(int $interval): string
    {
        $days = collect($this->days_of_week ?? [])
            ->map(fn ($day) => self::DAY_LABELS[(int) $day] ?? null)
            ->filter()
            ->implode(', ');

        $prefix = $interval > 1 ? "Setiap {$interval} minggu" : 'Mingguan';

        return $days ? "{$prefix}: {$days}" : $prefix;
    }
}
