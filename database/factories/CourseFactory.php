<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
use App\Models\User;

class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_code' => fake()->unique()->word(),
            'course_name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'instructor_id' => User::where('role', 'instructor')->inRandomOrder()->first()->id,
        ];
    }
}
