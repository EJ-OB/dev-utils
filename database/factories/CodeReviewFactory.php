<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CodeReviewStatus;
use App\Models\CodeReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CodeReview>
 */
class CodeReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link' => $this->faker->url,
            'status' => CodeReviewStatus::PENDING,
            'description' => $this->faker->sentence,
            'approve_count' => 0,
        ];
    }
}
