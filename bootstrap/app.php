<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

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
        // 1. معالجة ValidationException - إرجاع JSON دائماً لمسارات API فقط
        $exceptions->render(function (ValidationException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // 2. Handle AuthenticationException for API and Admin routes
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

        // 3. معالجة أخطاء قاعدة البيانات - لمسارات API فقط
        $exceptions->render(function (QueryException $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                \Log::error('Database error in API', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'message' => 'Database error occurred',
                    'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
                ], 500);
            }
        });

        // 4. معالجة جميع الاستثناءات الأخرى - لمسارات API فقط
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                \Log::error('Unhandled exception in API', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                
                return response()->json([
                    'message' => $e->getMessage(),
                    'error' => class_basename($e),
                ] + (config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : []), $statusCode);
            }
        });
    })->create();
