<?php

namespace App\Policies;

use App\Models\Entry;
use App\Models\Review;
use App\Models\User;
use App\Support\Sandbox;

class ReviewPolicy
{
    /** Members review other people's uploads, not their own. */
    public function create(User $user, Entry $entry): bool
    {
        return $user->id !== $entry->user_id;
    }

    /** The reviewer, or a moderator. */
    public function delete(User $user, Review $review): bool
    {
        return ($user->id === $review->user_id || $user->can('moderate-community')) && Sandbox::owns($review);
    }
}
