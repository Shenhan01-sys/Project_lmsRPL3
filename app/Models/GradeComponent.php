<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'name',
        'description',
        'weight',
        'max_score',
        'is_active',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'max_score' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke Course
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relasi ke Grades (nilai-nilai siswa untuk komponen ini)
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Scope untuk komponen yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get weight dalam format persentase
     */
    public function getWeightPercentageAttribute()
    {
        return $this->weight . '%';
    }
}