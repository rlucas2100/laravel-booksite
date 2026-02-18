<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];


    public function podcasts()
    {
        return $this->hasMany(Podcast::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
