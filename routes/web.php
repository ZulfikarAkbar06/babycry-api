<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app'       => 'Baby Cry IoT API',
        'status'    => 'running',
        'endpoints' => url('/api/cry-logs'),
    ]);
});
