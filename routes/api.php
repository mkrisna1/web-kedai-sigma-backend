<?php

use Illuminate\Support\Facades\Route;

// Import Controller
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\LaporanController;
use App\Http\Controllers\Api\Admin\MejaController as AdminMejaController;
use App\Http\Controllers\Api\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Api\Admin\PesananController as AdminPesananController;
use App\Http\Controllers\Api\Admin\ReservasiController as AdminReservasiController;
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\Order\CheckoutController;
use App\Http\Controllers\Api\Order\QrMenuController;
use App\Http\Controllers\Public\KategoriController;
use App\Http\Controllers\Public\MenuController;
use App\Http\Controllers\Public\ReservasiController;
use App\Http\Controllers\Public\ReviewController;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/menu', [AdminMenuController::class, 'index']);
    Route::post('/menu', [AdminMenuController::class, 'store']);
    Route::patch('/menu/{produk}', [AdminMenuController::class, 'update']);
    Route::delete('/menu/{produk}', [AdminMenuController::class, 'destroy']);
    Route::get('/kategori', [AdminMenuController::class, 'categories']);

    Route::get('/meja', [AdminMejaController::class, 'index']);
    Route::post('/meja', [AdminMejaController::class, 'store']);
    Route::patch('/meja/{meja}', [AdminMejaController::class, 'update']);
    Route::delete('/meja/{meja}', [AdminMejaController::class, 'destroy']);
    Route::post('/meja/{meja}/qr', [AdminMejaController::class, 'generateQr']);

    Route::get('/pesanan', [AdminPesananController::class, 'index']);
    Route::patch('/pesanan/{pesanan}/status', [AdminPesananController::class, 'updateStatus']);
    Route::patch('/pesanan/{pesanan}/payment', [AdminPesananController::class, 'updatePayment']);
    Route::patch('/pesanan/{pesanan}/stock-issue', [AdminPesananController::class, 'resolveStockIssue']);
    Route::get('/pesanan/{pesanan}/receipt', [AdminPesananController::class, 'receipt']);

    Route::get('/reservasi', [AdminReservasiController::class, 'index']);
    Route::patch('/reservasi/{reservasi}/status', [AdminReservasiController::class, 'updateStatus']);
    Route::delete('/reservasi/{reservasi}', [AdminReservasiController::class, 'destroy']);

    Route::get('/review', [AdminReviewController::class, 'index']);
    Route::patch('/review/{review}/reply', [AdminReviewController::class, 'reply']);
    Route::delete('/review/{review}/photos/{photoIndex}', [AdminReviewController::class, 'destroyPhoto'])
        ->whereNumber('photoIndex');
    Route::delete('/review/{review}', [AdminReviewController::class, 'destroy']);

    Route::get('/laporan', [LaporanController::class, 'index']);
});

// public
Route::prefix('public')->group(function () {
    // Menu & Kategori (udah ada)
    Route::get('/kategori', [KategoriController::class, 'index']);
    Route::get('/menu', [MenuController::class, 'index']);
    Route::get('/best-seller', [MenuController::class, 'bestSeller']);
    Route::get('/menu/{id}', [MenuController::class, 'show']);

    // Reservasi
    Route::get('/reservasi/meja', [ReservasiController::class, 'tables']);
    Route::post('/reservasi', [ReservasiController::class, 'store']);

    // Review
    Route::get('/review', [ReviewController::class, 'index']);
    Route::post('/review', [ReviewController::class, 'store']);
    Route::post('/review/{review}/like', [ReviewController::class, 'like'])->middleware('throttle:20,1');
});

Route::prefix('qr')->group(function () {
    Route::get('/menu', [QrMenuController::class, 'index']);
    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::get('/payment/config', [CheckoutController::class, 'paymentConfig']);
    Route::get('/payment/{pesanan}/status', [CheckoutController::class, 'paymentStatus']);
});

Route::post('/payment/midtrans/notification', [CheckoutController::class, 'notification']);
