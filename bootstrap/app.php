<?php

use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsurePortalCustomer;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            require base_path('routes/admin.php');
            require base_path('routes/portal.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => EnsurePermission::class,
            'portal.customer' => EnsurePortalCustomer::class,
        ]);

        $middleware->redirectGuestsTo(
            fn ($request) => $request->is('portal*') ? route('portal.login') : route('admin.login')
        );

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->api(prepend: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
