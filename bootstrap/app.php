<?php

use App\Http\Middleware\EnsureIotApiKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Render berada di belakang proxy HTTPS; tanpa ini URL yang dihasilkan menjadi http://
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'iot.key' => EnsureIotApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Semua error di /api/* selalu dijawab JSON (walau tanpa header Accept: application/json)
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data atau endpoint tidak ditemukan',
                ], 404);
            }
        });
    })->create();
