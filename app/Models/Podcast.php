<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Podcast extends Model
{
    use HasFactory;

    protected $guarded = [];


//    define the relationship with the comments table
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }


    // a podcast belongs to a category
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

//  a podcast belongs to a user
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
