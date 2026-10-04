<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Melindungi endpoint tulis (POST/PUT/PATCH/DELETE) dengan header X-API-KEY.
 * Jika IOT_API_KEY di environment kosong, pemeriksaan dilewati (berguna saat awal pengujian).
 */
class EnsureIotApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('iot.api_key');

        if ($expected === '') {
            return $next($request);
        }

        $given = (string) $request->header('X-API-KEY', '');

        if (! hash_equals($expected, $given)) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak valid',
            ], 401);
        }

        return $next($request);
    }
}
