<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\InstallOnFirstRequest;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SweepSandbox;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(
            prepend: [InstallOnFirstRequest::class],
            append: [SweepSandbox::class, SecurityHeaders::class],
        );

        $middleware->alias(['admin' => EnsureAdmin::class]);
        $middleware->preventRequestForgery(except: ['stripe/webhook']);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
