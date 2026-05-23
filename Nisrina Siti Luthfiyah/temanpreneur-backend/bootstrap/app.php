<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Use Laravel's native HandleCors middleware. Avoid aliasing to Fruitcake to
// prevent autoload issues in some Windows/dev environments.

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        // Custom middleware alias
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'premium' => \App\Http\Middleware\PremiumMiddleware::class,
            'business.owner' => \App\Http\Middleware\CheckBusinessOwner::class,
        ]);

        // API middleware stack
       $middleware->api(prepend: [
    \Illuminate\Http\Middleware\HandleCors::class,
]);
    })

   ->withExceptions(function (Exceptions $exceptions): void {

    // ✅ FIX AUTH (INI PENTING BANGET)
    $exceptions->render(function (
        \Illuminate\Auth\AuthenticationException $e,
        \Illuminate\Http\Request $request
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated'
        ], 401);
    });

    // Not Found (404 API)
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

    // Error umum (500)
$exceptions->render(function (
        \Throwable $e,
        \Illuminate\Http\Request $request
    ) {
        if ($request->is('api/*')) {

            // 🔥 kalau debug aktif → tampilkan error asli
            if (config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 500);
            }

            // 🔒 production tetap aman
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server'
            ], 500);
        }
    });
})

    ->create();