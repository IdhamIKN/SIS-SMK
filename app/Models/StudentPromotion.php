<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPromotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'from_class_id',
        'to_class_id',
        'promotion_type',
        'reason',
        'promotion_date',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'promotion_date' => 'date',
    ];

    /**
     * Get the student that was promoted
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    /**
     * Get the academic year
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get the source class
     */
    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'from_class_id');
    }

    /**
     * Get the target class
     */
    public function toClass(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'to_class_id');
    }

    /**
     * Get the user who approved the promotion
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
