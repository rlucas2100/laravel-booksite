<?php

namespace App\Http\Middleware;

use App\Support\Sandbox;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * In demo mode, clears out what visitors made more than a day ago.
 * Shared hosting has no scheduler, so a request does it, at most every 30 minutes.
 */
class SweepSandbox
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Sandbox::enabled() && Cache::add('sandbox:swept', true, now()->addMinutes(30))) {
            rescue(fn () => Sandbox::prune());
        }

        return $response;
    }
}
