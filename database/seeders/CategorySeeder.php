<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public const CATEGORIES = [
        'adventure' => ['Adventure', 'Maps, storms and narrow escapes.'],
        'behind-the-books' => ['Behind the Books', 'How to read more, and enjoy it.'],
        'big-ideas' => ['Big Ideas', 'Books to argue with.'],
        'childrens' => ['Children’s', 'For reading aloud, at any age.'],
        'gothic-horror' => ['Gothic & Horror', 'Castles, monsters and things that go bump.'],
        'mystery' => ['Mystery', 'Clues, culprits and one more chapter before bed.'],
        'plays-poetry' => ['Plays & Poetry', 'Best read out loud.'],
        'romance' => ['Romance', 'Slow burns, sharp tongues and happy endings (mostly).'],
        'science-fiction' => ['Science Fiction', 'Yesterday’s guesses about tomorrow.'],
        'short-reads' => ['Short Reads', 'Done in an evening.'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $slug => [$name, $blurb]) {
            Category::query()->updateOrCreate(['slug' => $slug], ['name' => $name, 'blurb' => $blurb]);
        }
    }
}
