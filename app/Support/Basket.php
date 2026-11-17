<?php

namespace App\Support;

use App\Models\Entry;
use Illuminate\Database\Eloquent\Collection;

/** The shopping basket: a list of entry ids kept in the visitor's session. Everything is digital, so one of each. */
class Basket
{
    private const KEY = 'basket';

    /** @return list<int> */
    public static function ids(): array
    {
        return array_values(array_unique(array_map('intval', (array) session(self::KEY, []))));
    }

    public static function count(): int
    {
        return count(static::ids());
    }

    public static function has(Entry $entry): bool
    {
        return in_array($entry->id, static::ids(), true);
    }

    public static function add(Entry $entry): void
    {
        session([self::KEY => array_values(array_unique([...static::ids(), $entry->id]))]);
    }

    public static function remove(Entry $entry): void
    {
        session([self::KEY => array_values(array_diff(static::ids(), [$entry->id]))]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    /** The entries in the basket that can still be bought (priced, published, visible to this visitor). */
    public static function entries(): Collection
    {
        $ids = static::ids();

        if ($ids === []) {
            return new Collection;
        }

        return Entry::query()->published()->whereKey($ids)->whereNotNull('price')->with('category')->orderBy('title')->get();
    }

    public static function total(Collection $entries): int
    {
        return (int) $entries->sum('price');
    }
}
