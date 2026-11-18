<?php

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
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'branch.access' => \App\Http\Middleware\EnsureBranchAccess::class,
        ]);

        // Railway (and most PaaS) terminate TLS at the edge and forward plain HTTP,
        // so trust that edge's forwarded headers for correct scheme/IP detection.
        $middleware->trustProxies(at: '*');

        // A page visited before a deploy can otherwise come back blank on
        // browser back/forward - restored from bfcache instead of re-fetched,
        // referencing a previous deploy's hashed assets.
        $middleware->appendToGroup('web', \App\Http\Middleware\PreventStaleCache::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
