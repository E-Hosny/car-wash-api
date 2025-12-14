<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle AuthenticationException for API and Admin routes
        $exceptions->render(function (AuthenticationException $e, \Illuminate\Http\Request $request) {
            // For API routes, return JSON response instead of redirect
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error' => 'Authentication required'
                ], 401);
            }
            
            // For admin routes, redirect to admin login
            if ($request->is('admin/*') || $request->routeIs('admin.*')) {
                return redirect()->route('admin.login');
            }
            
            // Default: redirect to admin login for web requests
            return redirect()->route('admin.login');
        });
    })->create();
