<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\CourseModule;
use App\Models\Material;
use App\Models\Assignment;
use App\Models\Submission;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Instructors
        $instructors = User::factory(10)->create(['role' => 'instructor']);

        // Create Students
        $students = User::factory(50)->create(['role' => 'student']);

        // Create Courses for each instructor
        $instructors->each(function ($instructor) {
            Course::factory(2)->create(['instructor_id' => $instructor->id]);
        });

        // Create Course Modules, Materials, and Assignments for each course
        Course::all()->each(function ($course) {
            CourseModule::factory(5)->create(['course_id' => $course->id])->each(function ($module) {
                Material::factory(3)->create(['module_id' => $module->id]);
            });
            Assignment::factory(3)->create(['course_id' => $course->id]);
        });

        // Enroll students in courses
        $courses = Course::all();
        $students->each(function ($student) use ($courses) {
            if ($courses->count() >= 5) {
                $enrolledCourses = $courses->random(5);
                $enrolledCourses->each(function ($course) use ($student) {
                    Enrollment::factory()->create([
                        'student_id' => $student->id,
                        'course_id' => $course->id,
                    ]);

                    // Create submissions for some assignments in the enrolled course
                    if ($course->assignments->count() >= 2) {
                        $assignments = $course->assignments->random(2);
                        $assignments->each(function ($assignment) use ($student) {
                            Submission::factory()->create([
                                'assignment_id' => $assignment->id,
                                'student_id' => $student->id,
                            ]);
                        });
                    }
                });
            }
        });
    }
}
