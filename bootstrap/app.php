<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Đăng ký middleware aliases
        $middleware->alias([
            'admin-only' => \App\Http\Middleware\AdminOnlyMiddleware::class,
            'customer-or-admin' => \App\Http\Middleware\CustomerOrAdminMiddleware::class,
        ]);
        
        // Thêm middleware cho API routes
        $middleware->api(prepend: [
            \App\Http\Middleware\FormatApiResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Xử lý AuthenticationException cho API
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*') || str_starts_with($request->path(), 'api/')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error_code' => 'AUTH_001',
                ], 401);
            }
        });
    })->create();
