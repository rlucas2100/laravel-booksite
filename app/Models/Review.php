<?php

namespace App\Models;

use App\Models\Concerns\Sandboxed;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reader's verdict on one entry: 0 to 5 stars, with an optional headline and text.
 * Each member gets one review per entry; posting again replaces it.
 */
class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory, Sandboxed;

    public const MIN = 0;

    public const MAX = 5;

    /** What each number of stars means, shown beside the star picker. */
    public const WORDS = [
        0 => "Couldn't finish it",
        1 => 'Not for me',
        2 => 'It was okay',
        3 => 'Good',
        4 => 'Really good',
        5 => "Couldn't put it down!",
    ];

    protected $fillable = ['rating', 'title', 'body'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function words(): string
    {
        return self::WORDS[$this->rating] ?? '';
    }
}
