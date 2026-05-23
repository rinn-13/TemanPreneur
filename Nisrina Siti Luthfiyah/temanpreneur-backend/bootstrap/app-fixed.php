<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        // =========================
        // MIDDLEWARE ALIAS
        // =========================
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'premium' => \App\Http\Middleware\PremiumMiddleware::class,
            'business.owner' => \App\Http\Middleware\CheckBusinessOwner::class,
        ]);

        // =========================
        // API MIDDLEWARE STACK (FIXED)
        // =========================
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })

    // =========================
    // EXCEPTION HANDLING
    // =========================
    ->withExceptions(function (Exceptions $exceptions): void {

        // Unauthenticated
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            \Illuminate\Http\Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        });

        // 404 Not Found
        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource tidak ditemukan',
                ], 404);
            }
        });

        // 500 Server Error
        $exceptions->render(function (
            \Throwable $e,
            \Illuminate\Http\Request $request
        ) {
            if ($request->is('api/*')) {

                if (config('app.debug')) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ], 500);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan pada server'
                ], 500);
            }
        });
    })

    // =========================
    // CORS CONFIG
    // =========================
    ->withCors(
        allowedOrigins: [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost:3000'
        ],
        allowedMethods: ['*'],
        allowedHeaders: ['*'],
        allowsCredentials: false, // 🔥 penting untuk Bearer token (bukan cookie)
        exposedHeaders: ['Authorization'],
        maxAge: 0,
    )

    ->create();