<?php

namespace Database\Factories;

use App\Models\Entry;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(Review::MIN, Review::MAX),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
        ];
    }
}
