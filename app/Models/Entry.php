<?php

namespace App\Models;

use App\Enums\EntryType;
use App\Models\Concerns\Sandboxed;
use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

/**
 * One thing on the shelves: a book, a video, an audio episode or an article.
 *
 * They all share this one table. The "type" column (see App\Enums\EntryType)
 * says which it is, so categories, reviews and comments only ever need to
 * point at one place: entries.id.
 */
class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory, Sandboxed;

    public const DISK = 'local';

    public const SORTS = [
        'new' => 'Newest',
        'top' => 'Top rated',
        'talked' => 'Most talked about',
        'az' => 'A to Z',
    ];

    protected $fillable = ['title', 'creator', 'category_id', 'year', 'description', 'body', 'link'];

    protected function casts(): array
    {
        return [
            'type' => EntryType::class,
            'featured' => 'boolean',
            'published_at' => 'datetime',
            'year' => 'integer',
            'duration' => 'integer',
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Take the files off the disk when the entry goes.
        static::deleting(function (Entry $entry) {
            Storage::disk(self::DISK)->delete(array_filter([$entry->cover_path, $entry->file_path]));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ---------------------------------------------------------------- relationships */

    // An entry belongs to the user who uploaded it.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // An entry belongs to a category.
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // An entry has many reviews (0 to 5 stars each) and many comments.
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /* ---------------------------------------------------------------- scopes */

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeOfType(Builder $query, EntryType $type): void
    {
        $query->where('type', $type->value);
    }

    /** Adds reviews_avg_rating, reviews_count and comments_count to each row. */
    public function scopeWithRatings(Builder $query): void
    {
        $query->withAvg('reviews', 'rating')->withCount(['reviews', 'comments']);
    }

    /** Title, creator, description or category name contains the words. */
    public function scopeSearch(Builder $query, string $term): void
    {
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        // Wrapped in one group, so it can't leak past the other "where"s.
        $query->where(function (Builder $query) use ($like) {
            $query->where('title', 'like', $like)
                ->orWhere('creator', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like));
        });
    }

    /** Needs withRatings() for "top" and "talked". */
    public function scopeSorted(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'top' => $query->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count')->orderBy('title'),
            'talked' => $query->orderByDesc('comments_count')->orderByDesc('reviews_count')->orderBy('title'),
            'az' => $query->orderBy('title'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
    }

    /* ---------------------------------------------------------------- helpers */

    /** A slug nobody else is using: "dracula", "dracula-2"... */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($title) ?: 'untitled', 80, '');
        $slug = $base;

        for ($n = 2; static::withoutGlobalScopes()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $n++) {
            $slug = $base.'-'.$n;
        }

        return $slug;
    }

    /** The public address: /books/dracula, /audio/the-raven... */
    public function url(): string
    {
        return route('entries.show', [$this->type->segment(), $this->slug]);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? route('media.cover', $this).'?v='.$this->updated_at?->timestamp : null;
    }

    public function fileUrl(bool $download = false): ?string
    {
        return $this->file_path ? route('media.file', $this).($download ? '?download=1' : '') : null;
    }

    public function fileExtension(): ?string
    {
        return $this->file_path ? strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)) : null;
    }

    /** The Content-Type to send, taken from our own list and never from the upload. */
    public function fileContentType(): string
    {
        return $this->type->fileTypes()[$this->fileExtension()] ?? 'application/octet-stream';
    }

    public function downloadName(): string
    {
        return $this->slug.'.'.$this->fileExtension();
    }

    /** Average stars to one decimal place, or null when nobody has reviewed it. Needs withRatings(). */
    public function rating(): ?float
    {
        $average = $this->getAttribute('reviews_avg_rating');

        return $average === null ? null : round((float) $average, 1);
    }

    /** "12 sec", "3 min", "1 hr 12 min". */
    public function durationLabel(): ?string
    {
        if (! $this->duration) {
            return null;
        }

        if ($this->duration < 60) {
            return $this->duration.' sec';
        }

        $minutes = max(1, (int) round($this->duration / 60));

        return $minutes < 60 ? $minutes.' min' : intdiv($minutes, 60).' hr '.($minutes % 60).' min';
    }

    public function fileSizeLabel(): ?string
    {
        return $this->file_size ? Number::fileSize($this->file_size, precision: 0, maxPrecision: 1) : null;
    }

    /** An article's Markdown as safe HTML: raw HTML is removed and unsafe links dropped. */
    public function bodyHtml(): string
    {
        // A link written as "/books/dracula" is pointed at this site's own address,
        // so it still works when the shop is installed in a sub-folder.
        $body = str_replace('](/', ']('.rtrim(url('/'), '/').'/', (string) $this->body);

        return Str::markdown($body, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    }

    /** Roughly how long an article takes to read. */
    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->body)) / 220));
    }
}
