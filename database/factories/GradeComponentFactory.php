<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GradeComponent>
 */
class GradeComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $componentTypes = [
            ['name' => 'UTS (Ujian Tengah Semester)', 'weight' => 30.00],
            ['name' => 'UAS (Ujian Akhir Semester)', 'weight' => 40.00],
            ['name' => 'Tugas Harian', 'weight' => 15.00],
            ['name' => 'Quiz', 'weight' => 10.00],
            ['name' => 'Partisipasi', 'weight' => 5.00],
            ['name' => 'Praktikum', 'weight' => 25.00],
            ['name' => 'Project', 'weight' => 20.00],
        ];

        $component = $this->faker->randomElement($componentTypes);

        return [
            'course_id' => Course::factory(),
            'name' => $component['name'],
            'description' => $this->faker->sentence(10),
            'weight' => $component['weight'],
            'max_score' => $this->faker->randomElement([100.00, 10.00, 4.00]),
            'is_active' => $this->faker->boolean(90), // 90% chance active
        ];
    }

    /**
     * Set specific weight for the component
     */
    public function withWeight(float $weight): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => $weight,
        ]);
    }

    /**
     * Set component as inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
