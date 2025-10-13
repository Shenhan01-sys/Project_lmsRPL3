<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GradingService
{
    /**
     * Buat komponen nilai untuk course
     *
     * @param int $courseId
     * @param array $data
     * @return GradeComponent
     * @throws \Exception
     */
    public function createGradeComponent(int $courseId, array $data)
    {
        // Validasi total bobot tidak melebihi 100%
        $existingWeight = GradeComponent::where('course_id', $courseId)
            ->where('is_active', true)
            ->sum('weight');

        if (($existingWeight + $data['weight']) > 100) {
            throw new \Exception("Total bobot melebihi 100%. Sisa bobot yang tersedia: " . (100 - $existingWeight) . "%");
        }

        return GradeComponent::create([
            'course_id' => $courseId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'weight' => $data['weight'],
            'max_score' => $data['max_score'] ?? 100.00,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Input nilai siswa
     *
     * @param int $studentId
     * @param int $gradeComponentId
     * @param float $score
     * @param array $options
     * @return Grade
     * @throws \Exception
     */
    public function inputGrade(int $studentId, int $gradeComponentId, float $score, array $options = [])
    {
        $gradeComponent = GradeComponent::findOrFail($gradeComponentId);
        
        // Validasi siswa terdaftar di course
        $isEnrolled = DB::table('enrollments')
            ->where('student_id', $studentId)
            ->where('course_id', $gradeComponent->course_id)
            ->exists();

        if (!$isEnrolled) {
            throw new \Exception("Siswa tidak terdaftar di course ini.");
        }

        // Validasi nilai tidak melebihi max_score
        $maxScore = $options['max_score'] ?? $gradeComponent->max_score;
        if ($score > $maxScore) {
            throw new \Exception("Nilai tidak boleh melebihi nilai maksimal ({$maxScore}).");
        }

        // Update atau create grade
        return Grade::updateOrCreate(
            [
                'student_id' => $studentId,
                'grade_component_id' => $gradeComponentId,
            ],
            [
                'score' => $score,
                'max_score' => $maxScore,
                'notes' => $options['notes'] ?? null,
                'graded_by' => $options['graded_by'] ?? (Auth::check() ? Auth::id() : null),
                'graded_at' => now(),
            ]
        );
    }

    /**
     * Input nilai massal (bulk)
     *
     * @param array $grades
     * @return Collection
     * @throws \Exception
     */
    public function bulkInputGrades(array $grades)
    {
        $results = collect();
        
        DB::beginTransaction();
        try {
            foreach ($grades as $gradeData) {
                $grade = $this->inputGrade(
                    $gradeData['student_id'],
                    $gradeData['grade_component_id'],
                    $gradeData['score'],
                    $gradeData['options'] ?? []
                );
                $results->push($grade);
            }
            
            DB::commit();
            return $results;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Hitung nilai akhir siswa untuk sebuah course
     *
     * @param int $studentId
     * @param int $courseId
     * @return array
     */
    public function calculateFinalGrade(int $studentId, int $courseId)
    {
        // Ambil semua komponen nilai yang aktif
        $gradeComponents = GradeComponent::where('course_id', $courseId)
            ->where('is_active', true)
            ->with(['grades' => function($query) use ($studentId) {
                $query->where('student_id', $studentId);
            }])
            ->get();

        $totalWeightedScore = 0;
        $totalWeight = 0;
        $details = [];

        foreach ($gradeComponents as $component) {
            $grade = $component->grades->first();
            
            if ($grade) {
                // Hitung nilai berbobot
                $percentage = $grade->percentage;
                $weightedScore = ($percentage * $component->weight) / 100;
                
                $totalWeightedScore += $weightedScore;
                $totalWeight += $component->weight;

                $details[] = [
                    'component_name' => $component->name,
                    'score' => $grade->score,
                    'max_score' => $grade->max_score,
                    'percentage' => $percentage,
                    'weight' => $component->weight,
                    'weighted_score' => $weightedScore,
                    'grade_letter' => $grade->grade_letter,
                ];
            } else {
                // Komponen belum dinilai
                $details[] = [
                    'component_name' => $component->name,
                    'score' => null,
                    'max_score' => $component->max_score,
                    'percentage' => null,
                    'weight' => $component->weight,
                    'weighted_score' => 0,
                    'grade_letter' => null,
                ];
            }
        }

        // Hitung nilai akhir
        $finalScore = $totalWeight > 0 ? $totalWeightedScore : 0;
        $finalGradeLetter = $this->determineGradeLetter($finalScore);

        return [
            'student_id' => $studentId,
            'course_id' => $courseId,
            'final_score' => round($finalScore, 2),
            'final_grade_letter' => $finalGradeLetter,
            'total_weight' => $totalWeight,
            'is_complete' => $totalWeight >= 100,
            'details' => $details,
        ];
    }

    /**
     * Tentukan predikat nilai
     *
     * @param float $score
     * @return string
     */
    public function determineGradeLetter(float $score)
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'E';
    }

    /**
     * Get nilai siswa untuk sebuah course
     *
     * @param int $studentId
     * @param int $courseId
     * @return Collection
     */
    public function getStudentGrades(int $studentId, int $courseId)
    {
        return Grade::whereHas('gradeComponent', function($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
        ->where('student_id', $studentId)
        ->with(['gradeComponent', 'grader:id,name'])
        ->get();
    }

    /**
     * Get rekap nilai untuk semua siswa di course
     *
     * @param int $courseId
     * @return Collection
     */
    public function getCourseGradesSummary(int $courseId)
    {
        // Ambil semua siswa yang terdaftar di course
        $enrolledStudents = DB::table('enrollments')
            ->join('users', 'enrollments.student_id', '=', 'users.id')
            ->where('enrollments.course_id', $courseId)
            ->select('users.id', 'users.name', 'users.email')
            ->get();

        $summary = collect();

        foreach ($enrolledStudents as $student) {
            $finalGrade = $this->calculateFinalGrade($student->id, $courseId);
            $finalGrade['student_name'] = $student->name;
            $finalGrade['student_email'] = $student->email;
            
            $summary->push($finalGrade);
        }

        return $summary;
    }

    /**
     * Validasi total bobot komponen nilai
     *
     * @param int $courseId
     * @return array
     */
    public function validateTotalWeight(int $courseId)
    {
        $components = GradeComponent::where('course_id', $courseId)
            ->where('is_active', true)
            ->get(['id', 'name', 'weight']);

        $totalWeight = $components->sum('weight');

        return [
            'total_weight' => $totalWeight,
            'is_valid' => $totalWeight == 100,
            'remaining_weight' => 100 - $totalWeight,
            'components' => $components,
        ];
    }

    /**
     * Get statistik nilai untuk course
     *
     * @param int $courseId
     * @return array
     */
    public function getCourseStatistics(int $courseId)
    {
        $summary = $this->getCourseGradesSummary($courseId);
        
        $completedGrades = $summary->where('is_complete', true);
        $scores = $completedGrades->pluck('final_score');

        if ($scores->isEmpty()) {
            return [
                'total_students' => $summary->count(),
                'completed_grades' => 0,
                'average_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'grade_distribution' => [
                    'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0
                ],
            ];
        }

        // Hitung distribusi nilai
        $gradeDistribution = $completedGrades->groupBy('final_grade_letter')
            ->map(function($group) {
                return $group->count();
            })
            ->toArray();

        return [
            'total_students' => $summary->count(),
            'completed_grades' => $completedGrades->count(),
            'average_score' => round($scores->average(), 2),
            'highest_score' => $scores->max(),
            'lowest_score' => $scores->min(),
            'grade_distribution' => array_merge([
                'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0
            ], $gradeDistribution),
        ];
    }
}