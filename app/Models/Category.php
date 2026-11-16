<?php

namespace App\Models;

use App\Models\Concerns\Sandboxed;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, Sandboxed;

    protected $fillable = ['name', 'slug', 'blurb'];

    // A category has many entries (of any type).
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** A slug nobody else is using, e.g. "mystery", "mystery-2". */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;

        for ($n = 2; static::withoutGlobalScopes()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $n++) {
            $slug = $base.'-'.$n;
        }

        return $slug;
    }
}
