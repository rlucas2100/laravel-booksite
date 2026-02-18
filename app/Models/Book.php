<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class Book extends Model
{
    use HasFactory;

//  protected $fillable = ['title', 'user_id', 'category_id', 'description'];

    protected $guarded = [];


// for searching books
//    public static function where(string $string, $id)
//    {
//    }

    public function scopeFilter($query)
    {
        if (request('search')) {
            $query
                ->where('title', 'like', '%' . request('search') . '%')
                ->orWhere('description', 'like', '%' . request('search') . '%')
                ->orWhere(function ($qry) {
                    $qry->whereHas('category', function ($qry) {
                        $qry->where('name', 'like', '%' . request('search') . '%');
                    });
                });
        }
    }

//return 'uuid'; changed to 'slug' to get book slug

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
//    a book has many comments

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');

    }

// a book belongs to a category
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

//  a book belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function showOnHomePage($no_of_books = 5)
    {
        return Book::latest()->filter()->paginate($no_of_books);
    }
}


