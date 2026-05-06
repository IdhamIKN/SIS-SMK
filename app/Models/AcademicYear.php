<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year_start',
        'year_end',
        'is_active',
        'promotion_deadline',
        'promotion_waves',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'promotion_deadline' => 'date',
        'promotion_waves' => 'array',
        'year_start' => 'integer',
        'year_end' => 'integer',
    ];

    /**
     * Get all classes for this academic year
     */
    public function classes(): HasMany
    {
        return $this->hasMany(Kelas::class, 'academic_year_id');
    }

    /**
     * Get all student promotions for this academic year
     */
    public function studentPromotions(): HasMany
    {
        return $this->hasMany(StudentPromotion::class, 'academic_year_id');
    }

    /**
     * Scope for active academic year
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if promotion is allowed
     */
    public function canPromote(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->promotion_deadline && now()->isAfter($this->promotion_deadline)) {
            return false;
        }

        return true;
    }
}
