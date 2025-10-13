<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\GradeComponent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxScore = $this->faker->randomElement([100.00, 10.00, 4.00]);
        $scorePercentage = $this->faker->numberBetween(60, 95); // 60-95% score
        $score = ($scorePercentage / 100) * $maxScore;

        $notes = [
            'Bagus! Pertahankan prestasi.',
            'Perlu ditingkatkan lagi.',
            'Sangat baik!',
            'Cukup memuaskan.',
            null, // 20% chance no notes
        ];

        return [
            'student_id' => User::factory()->state(['role' => 'student']),
            'grade_component_id' => GradeComponent::factory(),
            'score' => round($score, 2),
            'max_score' => $maxScore,
            'notes' => $this->faker->randomElement($notes),
            'graded_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
            'graded_by' => User::factory()->state(['role' => 'instructor']),
        ];
    }

    /**
     * Set a specific score for the grade
     */
    public function withScore(float $score, float $maxScore = 100.00): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => $score,
            'max_score' => $maxScore,
        ]);
    }

    /**
     * Set excellent score (90-100%)
     */
    public function excellent(): static
    {
        return $this->state(function (array $attributes) {
            $maxScore = $attributes['max_score'] ?? 100.00;
            $scorePercentage = $this->faker->numberBetween(90, 100);
            return [
                'score' => round(($scorePercentage / 100) * $maxScore, 2),
                'notes' => 'Excellent work! Outstanding performance.',
            ];
        });
    }

    /**
     * Set poor score (50-60%)
     */
    public function poor(): static
    {
        return $this->state(function (array $attributes) {
            $maxScore = $attributes['max_score'] ?? 100.00;
            $scorePercentage = $this->faker->numberBetween(50, 60);
            return [
                'score' => round(($scorePercentage / 100) * $maxScore, 2),
                'notes' => 'Need significant improvement.',
            ];
        });
    }
}
