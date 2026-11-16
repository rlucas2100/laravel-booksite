<?php

namespace App\Http\Middleware;

use App\Support\Installer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** With SHOP_AUTO_INSTALL=true, builds and fills the database the first time the site is opened. */
class InstallOnFirstRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('shop.auto_install') && ! Installer::installed()) {
            Installer::run();
        }

        return $next($request);
    }
}
