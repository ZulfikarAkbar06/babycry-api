<?php

use App\Http\Controllers\Api\CryLogController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Cek status API + koneksi database (berguna untuk debugging setelah deploy)
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        $db = 'connected';
    } catch (\Throwable $e) {
        $db = 'error';
    }

    return response()->json([
        'status'   => 'ok',
        'database' => $db,
        'time'     => now()->toIso8601String(),
    ]);
});

// Endpoint khusus (harus sebelum apiResource agar tidak dianggap {id})
Route::get('/cry-logs/latest', [CryLogController::class, 'latest']);
Route::get('/cry-logs/summary', [CryLogController::class, 'summary']);

// Baca data: terbuka
Route::apiResource('cry-logs', CryLogController::class)->only(['index', 'show']);

// Tulis data: dilindungi X-API-KEY (jika IOT_API_KEY diisi)
Route::middleware('iot.key')->group(function () {
    Route::apiResource('cry-logs', CryLogController::class)
        ->only(['store', 'update', 'destroy']);
});
