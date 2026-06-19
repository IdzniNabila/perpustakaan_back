<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aktifkan CORS untuk semua request API
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle error JWT agar response-nya selalu JSON (bukan HTML)
        $exceptions->render(function (TokenExpiredException $e, Request $request) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Token has expired'],
            ], 401);
        });

        $exceptions->render(function (TokenInvalidException $e, Request $request) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Token is invalid'],
            ], 401);
        });

        $exceptions->render(function (JWTException $e, Request $request) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Token not provided'],
            ], 401);
        });
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Unauthenticated'],
            ], 401);
        });
    })->create();
