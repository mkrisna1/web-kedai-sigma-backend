<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Kedai Sigma Backend API',
        'frontend_url' => env('FRONTEND_URL', 'http://127.0.0.1:5173'),
    ]);
});

Route::get('/local-storage/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*');
