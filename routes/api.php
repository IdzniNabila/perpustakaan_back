<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistem Manajemen Perpustakaan
|--------------------------------------------------------------------------
|
| Semua route di sini otomatis dapat prefix /api oleh Laravel.
| Auth guard menggunakan JWT via middleware 'auth:api'.
|
*/

// ── Autentikasi (publik, tidak perlu token) ─────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',   [AuthController::class, 'login']);
    Route::post('signup',  [AuthController::class, 'signup']);

    // Endpoint berikut butuh token yang masih valid
    Route::middleware('auth:api')->group(function () {
        Route::post('logout',  [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me',       [AuthController::class, 'me']);
    });
});

// ── Resource yang dilindungi JWT ────────────────────────────────────────────
Route::middleware('auth:api')->group(function () {

    // Buku
    Route::get('book/{book}/edit', [BookController::class, 'edit']);
    Route::apiResource('book', BookController::class);

    // User / Anggota
    Route::get('user/{user}/edit', [UserController::class, 'edit']);
    Route::apiResource('user', UserController::class);

    // Peminjaman
    Route::get('peminjaman/create',         [PeminjamanController::class, 'create']);
    Route::get('peminjaman/{peminjaman}/edit', [PeminjamanController::class, 'edit']);
    Route::apiResource('peminjaman', PeminjamanController::class);
});
