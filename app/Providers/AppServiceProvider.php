<?php

namespace App\Providers;

use App\Enums\EntryType;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Catch "N+1" queries while developing: a relationship has to be loaded on purpose.
        Model::preventLazyLoading(! $this->app->isProduction());

        // The Super-Admin role is granted everything. Works with $user->can() and @can().
        Gate::before(fn (User $user, string $ability) => $user->isOwner() ? true : null);

        // /books, /videos, /audio and /blog turn into an EntryType.
        Route::bind('shelf', fn (string $value) => EntryType::fromSegment($value) ?? abort(404));

        Paginator::defaultView('partials.pagination');

        // Some shared hosts don't tell PHP the visitor came by https, so links would be written as http.
        if ($this->app->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn () => Password::min(8));

        // Posting reviews and comments, and uploading, are limited per member (or per address).
        RateLimiter::for('community', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('uploads', fn (Request $request) => Limit::perMinute(6)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('sign-up', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
