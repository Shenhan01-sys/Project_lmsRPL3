<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Submission>
 */
use App\Models\Assignment;
use App\Models\User;

class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::inRandomOrder()->first()->id,
            'student_id' => User::where('role', 'student')->inRandomOrder()->first()->id,
            'file_path' => fake()->filePath(),
            'grade' => fake()->randomFloat(2, 50, 100),
            'feedback' => fake()->paragraph(),
        ];
    }
}
