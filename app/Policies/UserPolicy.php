<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Sandbox;

/**
 * The owner (Super-Admin) passes every check before these run, so the rules
 * below are the limits on an ordinary admin.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage-users');
    }

    /** Admins manage members, not each other, and never the protected demo accounts. */
    public function update(User $user, User $target): bool
    {
        return $user->can('manage-users')
            && ! $target->protected
            && ! $target->isAdmin()
            && Sandbox::owns($target);
    }

    public function delete(User $user, User $target): bool
    {
        return $user->id !== $target->id && $this->update($user, $target);
    }

    /** Only the owner hands out the Admin role. */
    public function changeRole(User $user, User $target): bool
    {
        return false;
    }
}
