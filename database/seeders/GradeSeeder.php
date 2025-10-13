<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\User;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua grade components yang ada
        $gradeComponents = GradeComponent::with('course')->where('is_active', true)->get();

        if ($gradeComponents->isEmpty()) {
            $this->command->warn('Tidak ada grade components yang ditemukan. Pastikan GradeComponentSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        // Ambil instructor untuk graded_by (yang memiliki role instructor)
        $instructors = User::where('role', 'instructor')->get();
        
        if ($instructors->isEmpty()) {
            $this->command->warn('Tidak ada instructor yang ditemukan. Menggunakan user pertama sebagai grader.');
            $instructors = User::take(1)->get();
        }

        $gradedCount = 0;

        foreach ($gradeComponents as $component) {
            // Ambil semua siswa yang enrolled di course ini
            $enrolledStudents = Enrollment::where('course_id', $component->course_id)
                ->with('student')
                ->get();

            if ($enrolledStudents->isEmpty()) {
                $this->command->warn("Tidak ada siswa yang enrolled di course: {$component->course->course_name}");
                continue;
            }

            $this->command->info("Membuat nilai untuk komponen: {$component->name} di course: {$component->course->course_name}");

            foreach ($enrolledStudents as $enrollment) {
                $student = $enrollment->student;
                $instructor = $instructors->random(); // Random instructor sebagai grader

                // Generate nilai random yang realistis
                $scorePercentage = $this->generateRealisticScore($component->name);
                $score = ($scorePercentage / 100) * $component->max_score;

                // Buat catatan berdasarkan nilai
                $notes = $this->generateNotes($scorePercentage, $component->name);

                Grade::create([
                    'student_id' => $student->id,
                    'grade_component_id' => $component->id,
                    'score' => round($score, 2),
                    'max_score' => $component->max_score,
                    'notes' => $notes,
                    'graded_at' => Carbon::now()->subDays(rand(1, 30)), // Random tanggal dalam 30 hari terakhir
                    'graded_by' => $instructor->id,
                ]);

                $gradedCount++;
            }
        }

        $this->command->info("Berhasil membuat {$gradedCount} nilai untuk semua siswa!");
    }

    /**
     * Generate score yang realistis berdasarkan jenis komponen
     */
    private function generateRealisticScore(string $componentName): float
    {
        $componentName = strtolower($componentName);

        if (str_contains($componentName, 'uts') || str_contains($componentName, 'ujian tengah')) {
            // UTS: distribusi normal dengan rata-rata 75
            return max(40, min(100, $this->normalDistribution(75, 12)));
        } elseif (str_contains($componentName, 'uas') || str_contains($componentName, 'ujian akhir')) {
            // UAS: distribusi normal dengan rata-rata 78 (biasanya sedikit lebih baik dari UTS)
            return max(45, min(100, $this->normalDistribution(78, 11)));
        } elseif (str_contains($componentName, 'tugas') || str_contains($componentName, 'quiz')) {
            // Tugas: distribusi normal dengan rata-rata 82 (biasanya lebih tinggi)
            return max(60, min(100, $this->normalDistribution(82, 10)));
        } elseif (str_contains($componentName, 'partisipasi') || str_contains($componentName, 'kehadiran')) {
            // Partisipasi: distribusi normal dengan rata-rata 85 (biasanya paling tinggi)
            return max(70, min(100, $this->normalDistribution(85, 8)));
        } else {
            // Default: distribusi normal dengan rata-rata 77
            return max(50, min(100, $this->normalDistribution(77, 10)));
        }
    }

    /**
     * Generate normal distribution (approximation using Box-Muller transform)
     */
    private function normalDistribution(float $mean, float $stdDev): float
    {
        static $spare = null;
        
        if ($spare !== null) {
            $retval = $spare * $stdDev + $mean;
            $spare = null;
            return $retval;
        }
        
        $u = mt_rand() / mt_getrandmax();
        $v = mt_rand() / mt_getrandmax();
        
        $mag = $stdDev * sqrt(-2.0 * log($u));
        $spare = $mag * cos(2.0 * pi() * $v);
        
        return $mag * sin(2.0 * pi() * $v) + $mean;
    }

    /**
     * Generate catatan berdasarkan nilai
     */
    private function generateNotes(float $scorePercentage, string $componentName): ?string
    {
        if ($scorePercentage >= 90) {
            $notes = [
                'Sangat baik! Pertahankan prestasi ini.',
                'Excellent work! Keep it up.',
                'Pemahaman materi sangat baik.',
                'Outstanding performance.',
            ];
        } elseif ($scorePercentage >= 80) {
            $notes = [
                'Bagus! Masih bisa ditingkatkan lagi.',
                'Good job! Room for improvement.',
                'Pemahaman materi cukup baik.',
                'Well done.',
            ];
        } elseif ($scorePercentage >= 70) {
            $notes = [
                'Cukup baik, perlu belajar lebih giat.',
                'Satisfactory, need more effort.',
                'Pemahaman materi cukup.',
                'Need to focus more on key concepts.',
            ];
        } elseif ($scorePercentage >= 60) {
            $notes = [
                'Perlu perbaikan dalam pemahaman materi.',
                'Need improvement in understanding.',
                'Sebaiknya konsultasi dengan dosen.',
                'Please see me during office hours.',
            ];
        } else {
            $notes = [
                'Perlu belajar lebih keras lagi.',
                'Significant improvement needed.',
                'Wajib mengikuti remedial.',
                'Please attend extra tutorial sessions.',
            ];
        }

        // 30% chance untuk tidak ada notes (null)
        if (rand(1, 100) <= 30) {
            return null;
        }

        return $notes[array_rand($notes)];
    }
}
