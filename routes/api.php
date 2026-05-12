<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

// Public routes
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (butuh token)
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});