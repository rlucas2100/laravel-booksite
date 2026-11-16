<?php

namespace Database\Factories;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Entry>
 */
class EntryFactory extends Factory
{
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(3, true));

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'type' => EntryType::Book,
            'title' => $title,
            'slug' => Str::slug($title),
            'creator' => fake()->name(),
            'description' => fake()->paragraph(),
            'year' => fake()->numberBetween(1800, 1925),
            'published_at' => now()->subMinute(),
        ];
    }

    public function type(EntryType $type): static
    {
        return $this->state(fn () => [
            'type' => $type,
            'body' => $type === EntryType::Article ? "## A heading\n\n".fake()->paragraphs(3, true) : null,
        ]);
    }
}
