<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'grade_component_id',
        'score',
        'max_score',
        'notes',
        'graded_at',
        'graded_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    /**
     * Relasi ke Student (User)
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Relasi ke Grade Component
     */
    public function gradeComponent()
    {
        return $this->belongsTo(GradeComponent::class);
    }

    /**
     * Relasi ke Grader (User yang memberi nilai)
     */
    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Hitung persentase nilai
     */
    public function getPercentageAttribute()
    {
        if ($this->max_score > 0) {
            return round(($this->score / $this->max_score) * 100, 2);
        }
        return 0;
    }

    /**
     * Hitung nilai dalam skala 100
     */
    public function getScaledScoreAttribute()
    {
        return $this->percentage;
    }

    /**
     * Tentukan predikat nilai (A, B, C, D, E)
     */
    public function getGradeLetterAttribute()
    {
        $percentage = $this->percentage;
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'E';
    }

    /**
     * Auto-set graded_at saat nilai di-input
     */
    protected static function booted()
    {
        static::creating(function ($grade) {
            if (is_null($grade->graded_at)) {
                $grade->graded_at = Carbon::now();
            }
        });
    }
}