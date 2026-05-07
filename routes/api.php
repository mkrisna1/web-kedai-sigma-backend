<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Route Public (Tidak perlu token)
Route::post('/admin/login', [AuthController::class, 'login']);

// Route Protected (Wajib pakai token dari admin guard)
Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'logout']);
    
    // Nanti semua route untuk nambah menu, edit meja, acc reservasi ditaruh di dalam sini
});