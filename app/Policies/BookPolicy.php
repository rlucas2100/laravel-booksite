<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use http\Env\Request;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
//      if ($this->isAdmin($user)) {
//
//      dd('test');
        return true;
//      }

    }


    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, Book $book)
    {
//      dd('view');

      return true;
    }



    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Book $book)
    {

      if ($user->id === $book->user_id || $user->hasPermissionTo('edit-books')) {

        return true;
      }
        return false;

    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Book $book)
    {

//            if ($book->user_id != Auth::id() && auth()->user()?->name !== 'Super Admin'
//        && auth()->user()?->name !== 'Admin' ) {
//        return abort(403);
//      }

      if ($user->id === $book->user_id || $user->hasPermissionTo('edit-books')) {

        return true;
      }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Book $book)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Book  $book
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Book $book)
    {
        //
    }

    private function isAdmin($user) {

      if ($user->hasRole('Super-Admin') || $user->hasRole('Admin')) {

        return true;
      }

        return false;
    }
}
