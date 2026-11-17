<?php

namespace App\Models;

use App\Models\Concerns\Sandboxed;
use App\Support\Sandbox;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, Sandboxed;

    public const OWNER_ROLE = 'Super-Admin';

    public const ADMIN_ROLE = 'Admin';

    protected static function booted(): void
    {
        // The database deletes a user's rows for us; this also removes their uploaded files.
        static::deleting(function (User $user) {
            Sandbox::pause(fn () => $user->entries()->get()->each->delete());
        });
    }

    /** A visitor's account must still be able to sign in, so users are never hidden globally. */
    protected static function hidesSandboxedByDefault(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'protected' => 'boolean',
        ];
    }

    // A user has many uploads, reviews and comments.

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** The ids of the entries this member has paid for. */
    public function purchasedIds(): array
    {
        return OrderItem::query()
            ->whereNotNull('entry_id')
            ->whereHas('order', fn ($order) => $order->where('user_id', $this->id)->where('status', Order::PAID))
            ->pluck('entry_id')->all();
    }

    /** May this member download or play the entry? Free ones always; priced ones once bought (or if it is theirs). */
    public function mayOpen(Entry $entry): bool
    {
        return $entry->isFree()
            || $entry->user_id === $this->id
            || $this->isOwner()
            || in_array($entry->id, $this->purchasedIds(), true);
    }

    /** The Super-Admin: passes every permission check (see AppServiceProvider). */
    public function isOwner(): bool
    {
        return $this->hasRole(self::OWNER_ROLE);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole([self::OWNER_ROLE, self::ADMIN_ROLE]);
    }

    public function roleLabel(): string
    {
        return match (true) {
            $this->isOwner() => 'Owner',
            $this->isAdmin() => 'Admin',
            default => 'Member',
        };
    }

    /** Up to two letters for the avatar bubble. */
    public function initials(): string
    {
        $words = preg_split('/[\s._-]+/u', trim($this->name), -1, PREG_SPLIT_NO_EMPTY) ?: ['?'];
        $letters = mb_substr($words[0], 0, 1).(count($words) > 1 ? mb_substr(end($words), 0, 1) : '');

        return mb_strtoupper($letters);
    }

    /** One of the shop colours, always the same for the same person. */
    public function colour(): string
    {
        $colours = ['tomato', 'sun', 'mint', 'cobalt', 'grape'];

        return $colours[$this->id % count($colours)];
    }
}
