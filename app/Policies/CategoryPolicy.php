<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Support\Sandbox;

class CategoryPolicy
{
    /** The admin list of categories. (Browsing a category is open to everyone.) */
    public function viewAny(User $user): bool
    {
        return $user->can('manage-categories');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-categories');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('manage-categories') && Sandbox::owns($category);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->update($user, $category);
    }
}
