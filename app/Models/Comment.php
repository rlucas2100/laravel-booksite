<?php

namespace App\Models;

use App\Models\Concerns\Sandboxed;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Part of the conversation under an entry. A comment can have replies
 * (one level deep): a reply is a comment with a parent_id.
 */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, Sandboxed;

    public const MAX_LENGTH = 1500;

    protected $fillable = ['body'];

    // A comment belongs to one entry and to the user who wrote it.
    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->oldest();
    }
}
