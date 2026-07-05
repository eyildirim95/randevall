<?php

use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\EnsureBusinessAccess;
use App\Http\Middleware\EnsureBusinessManager;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Reverse proxy / load balancer arkasinda dogru scheme+host (https callback'ler icin)
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'tenant' => IdentifyTenant::class,
            'business.access' => EnsureBusinessAccess::class,
            'subscription.active' => EnsureActiveSubscription::class,
            'business.manager' => EnsureBusinessManager::class,
            'superadmin' => EnsureSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
