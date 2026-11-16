<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Entry;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;

/**
 * Keeps a public demo tidy.
 *
 * With SHOP_DEMO=true, everything a visitor makes (an account, an upload, a
 * review, a comment, a category) is tagged with a random token that is kept in
 * a cookie. Tagged records are only shown to the browser that made them and are
 * deleted after a day, so nobody can post something that other visitors will
 * see, and the shelves always look the way the seeder left them.
 *
 * The owner (the Super-Admin account) sees and can change everything.
 * With SHOP_DEMO=false none of this applies.
 */
class Sandbox
{
    public const COOKIE = 'cliffhanger_visitor';

    private static int $paused = 0;

    public static function enabled(): bool
    {
        return (bool) config('shop.demo');
    }

    /** Run something (the seeder, the clean-up) with the sandbox switched off. */
    public static function pause(callable $callback): mixed
    {
        static::$paused++;

        try {
            return $callback();
        } finally {
            static::$paused--;
        }
    }

    /** True when the current request sees and may change everything. */
    public static function unrestricted(): bool
    {
        if (static::$paused > 0 || ! static::enabled()) {
            return true;
        }

        $user = auth()->user();

        return $user instanceof User && $user->isOwner();
    }

    /** This browser's token, if it has made anything yet. */
    public static function token(): ?string
    {
        $request = request();

        if ($minted = $request->attributes->get('sandbox_token')) {
            return $minted;
        }

        $cookie = $request->cookie(self::COOKIE);

        return is_string($cookie) && preg_match('/^[a-f0-9]{32}$/', $cookie) ? $cookie : null;
    }

    /** The tag for a record that is about to be created (null = public). */
    public static function tagForNewRecord(): ?string
    {
        if (static::unrestricted()) {
            return null;
        }

        if ($token = static::token()) {
            return $token;
        }

        $token = bin2hex(random_bytes(16));

        request()->attributes->set('sandbox_token', $token);
        Cookie::queue(Cookie::make(self::COOKIE, $token, 60 * 24 * 30, httpOnly: true, sameSite: 'lax'));

        return $token;
    }

    /** May the current visitor change or delete this record? */
    public static function owns(Model $record): bool
    {
        if (static::unrestricted()) {
            return true;
        }

        $tag = $record->getAttribute('sandbox');
        $token = static::token();

        return is_string($tag) && is_string($token) && hash_equals($tag, $token);
    }

    /** How many records of a kind this visitor has made. */
    public static function count(string $model): int
    {
        $token = static::token();

        return $token ? $model::query()->where('sandbox', $token)->count() : 0;
    }

    /** Delete what visitors made more than "sandbox_hours" ago. Returns how many records went. */
    public static function prune(): int
    {
        $before = now()->subHours((int) config('shop.sandbox_hours'));

        return static::pause(function () use ($before) {
            $deleted = 0;

            // Models are deleted one by one so their files are removed with them.
            foreach ([Comment::class, Review::class, Entry::class, User::class] as $model) {
                $model::query()->whereNotNull('sandbox')->where('created_at', '<', $before)->get()
                    ->each(function (Model $record) use (&$deleted) {
                        $record->delete();
                        $deleted++;
                    });
            }

            $deleted += Category::query()->whereNotNull('sandbox')->where('created_at', '<', $before)
                ->whereDoesntHave('entries')->delete();

            return $deleted;
        });
    }
}
