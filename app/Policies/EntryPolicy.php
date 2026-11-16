<?php

namespace App\Policies;

use App\Models\Entry;
use App\Models\User;
use App\Support\Sandbox;

class EntryPolicy
{
    /** Anyone, signed in or not, can browse the shelves. */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Entry $entry): bool
    {
        return true;
    }

    /** Every member can upload. */
    public function create(User $user): bool
    {
        return true;
    }

    /** The uploader, or anyone allowed to manage the catalogue. */
    public function update(User $user, Entry $entry): bool
    {
        return ($user->id === $entry->user_id || $user->can('manage-entries')) && Sandbox::owns($entry);
    }

    public function delete(User $user, Entry $entry): bool
    {
        return $this->update($user, $entry);
    }
}
