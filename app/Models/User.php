<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Octopy\Impersonate\Concerns\Impersonate;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

//    commented out to test if form will send
//    protected $fillable = [
//        'name',
//        'email',
//        'password',
//    ];
//
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

//    a user can have many posts
    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class);
    }

//    the user model has a one to many relationship with books
    public function books(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function podcasts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Podcast::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }


//  public static function showOnUserPage($no_of_users = 20) {
//    return User::latest()->filter()->paginate($no_of_users);
//  }

}

