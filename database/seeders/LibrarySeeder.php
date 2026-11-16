<?php

namespace Database\Seeders;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Entry;
use App\Models\User;
use App\Support\SamplerPdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Puts the books, audio, video and articles on the shelves. They are all rows
 * in the same "entries" table; only the type and a few columns differ.
 */
class LibrarySeeder extends Seeder
{
    private User $desk;

    /** @var array<string, int> category slug => id */
    private array $categories;

    private int $age = 0;

    public function run(): void
    {
        $this->desk = User::query()->where('email', 'desk@example.com')->firstOrFail();
        $this->categories = Category::query()->pluck('id', 'slug')->all();

        $books = require __DIR__.'/data/books.php';
        $media = require __DIR__.'/data/media.php';
        $articles = require __DIR__.'/data/articles.php';

        // Shuffled together by hand so the "newest" lists show a mix of everything.
        foreach ($articles as $i => $article) {
            $this->article($article, daysAgo: 1 + $i * 9);
        }

        foreach ($media['video'] as $i => $video) {
            $this->withFile(EntryType::Video, $video, daysAgo: 3 + $i * 6);
        }

        foreach ($media['audio'] as $i => $audio) {
            $this->withFile(EntryType::Audio, $audio, daysAgo: 2 + $i * 5);
        }

        foreach ($books as $i => $book) {
            $this->book($book, daysAgo: 2 + $i * 3);
        }
    }

    private function book(array $data, int $daysAgo): void
    {
        $link = 'https://www.gutenberg.org/ebooks/'.$data['gutenberg'];
        $path = 'seed/books/'.$data['slug'].'.pdf';

        // Every book gets a one-page PDF sampler, so there is a real file to download.
        $pdf = SamplerPdf::make(config('app.name'), $data['title'], $data['creator'], $data['opening'], $link);
        Storage::disk(Entry::DISK)->put($path, $pdf);

        $this->entry(EntryType::Book, $data, $daysAgo, [
            'body' => $data['opening'],
            'link' => $link,
            'file_path' => $path,
            'file_size' => strlen($pdf),
        ]);
    }

    private function withFile(EntryType $type, array $data, int $daysAgo): void
    {
        $source = __DIR__.'/media/'.$type->value.'/'.$data['file'];

        if (! is_file($source)) {
            $this->command?->warn("Skipped \"{$data['title']}\": {$data['file']} is not in database/seeders/media/{$type->value}.");

            return;
        }

        $path = 'seed/'.$type->value.'/'.$data['file'];
        Storage::disk(Entry::DISK)->put($path, file_get_contents($source));

        $this->entry($type, $data, $daysAgo, [
            'link' => $data['link'],
            'file_path' => $path,
            'file_size' => filesize($source),
            'duration' => $data['duration'],
        ]);
    }

    private function article(array $data, int $daysAgo): void
    {
        $this->entry(EntryType::Article, $data, $daysAgo, ['body' => $data['body'], 'year' => null]);
    }

    private function entry(EntryType $type, array $data, int $daysAgo, array $extra): void
    {
        $when = Carbon::now()->subDays($daysAgo)->setTime(9 + $this->age % 9, ($this->age * 17) % 60);
        $this->age++;

        $entry = Entry::query()->firstOrNew(['slug' => $data['slug']]);

        $entry->forceFill($extra + [
            'user_id' => $this->desk->id,
            'category_id' => $this->categories[$data['category']],
            'type' => $type,
            'title' => $data['title'],
            'creator' => $data['creator'],
            'year' => $data['year'] ?? null,
            'description' => $data['description'],
            'cover_design' => $data['design'] ?? null,
            'featured' => $data['featured'] ?? false,
            'published_at' => $when,
            'created_at' => $when,
            'updated_at' => $when,
        ])->save();
    }
}
