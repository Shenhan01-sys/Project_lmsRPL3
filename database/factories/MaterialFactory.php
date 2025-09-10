<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Material>
 */
use App\Models\CourseModule;

class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => CourseModule::inRandomOrder()->first()->id,
            'title' => fake()->sentence(2),
            'material_type' => fake()->randomElement(['file', 'link', 'video']),
            'content_path' => fake()->url(),
        ];
    }
}
