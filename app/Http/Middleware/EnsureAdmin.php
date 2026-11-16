<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Lets only admins and the owner into the admin area. (The 2023 version checked for a user called "Ryan".) */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'The admin area is for admins only.');

        return $next($request);
    }
}
