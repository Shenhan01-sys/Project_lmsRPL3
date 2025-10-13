<?php

namespace Database\Seeders;

use App\Models\GradeComponent;
use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua courses yang ada
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->warn('Tidak ada course yang ditemukan. Pastikan CourseSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        // Data template komponen nilai umum
        $gradeComponentTemplates = [
            [
                'name' => 'UTS (Ujian Tengah Semester)',
                'description' => 'Ujian tengah semester untuk mengukur pemahaman materi di pertengahan semester',
                'weight' => 30.00,
                'max_score' => 100.00,
            ],
            [
                'name' => 'UAS (Ujian Akhir Semester)',
                'description' => 'Ujian akhir semester untuk mengukur pemahaman keseluruhan materi',
                'weight' => 40.00,
                'max_score' => 100.00,
            ],
            [
                'name' => 'Tugas & Quiz',
                'description' => 'Penilaian dari tugas-tugas dan quiz selama semester',
                'weight' => 20.00,
                'max_score' => 100.00,
            ],
            [
                'name' => 'Partisipasi & Kehadiran',
                'description' => 'Penilaian keaktifan dan kehadiran dalam kelas',
                'weight' => 10.00,
                'max_score' => 100.00,
            ],
        ];

        // Buat komponen nilai untuk setiap course
        foreach ($courses as $course) {
            $this->command->info("Membuat komponen nilai untuk course: {$course->course_name}");
            
            foreach ($gradeComponentTemplates as $template) {
                GradeComponent::create([
                    'course_id' => $course->id,
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'weight' => $template['weight'],
                    'max_score' => $template['max_score'],
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Grade components berhasil dibuat untuk semua course!');
    }
}
