<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Support\Sandbox;

class CommentPolicy
{
    /** The writer, or a moderator. */
    public function delete(User $user, Comment $comment): bool
    {
        return ($user->id === $comment->user_id || $user->can('moderate-community')) && Sandbox::owns($comment);
    }
}
