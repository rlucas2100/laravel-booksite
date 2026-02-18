<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Podcast>
 */

class PodcastFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition()
    {
        return [
            'user_id' => rand(1,100),
            'category_id' => rand(1,10),
            'title' => fake()->unique()->word(),
            'description' => fake()->unique()->sentence(),
            'audio_url' => fake()->word(),
     ];
    }
}
