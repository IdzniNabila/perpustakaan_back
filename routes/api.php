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
    Route::post('login',   [AuthController::class, 'login'])->name('login');
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

    // Buku (Daftar buku bisa dilihat semua user yang login, modifikasi hanya untuk admin)
    Route::get('book', [BookController::class, 'index']);
    Route::get('book/{book}', [BookController::class, 'show']);

    // Peminjaman list can be accessed by anyone authenticated
    Route::get('peminjaman', [PeminjamanController::class, 'index']);

    Route::middleware('role:admin')->group(function () {
        Route::get('book/{book}/edit', [BookController::class, 'edit']);
        Route::post('book', [BookController::class, 'store']);
        Route::put('book/{book}', [BookController::class, 'update']);
        Route::delete('book/{book}', [BookController::class, 'destroy']);

        // User / Anggota
        Route::get('user/{user}/edit', [UserController::class, 'edit']);
        Route::apiResource('user', UserController::class);

        // Peminjaman (admin-only actions)
        Route::get('peminjaman/create',          [PeminjamanController::class, 'create']);
        Route::get('peminjaman/{peminjaman}/edit', [PeminjamanController::class, 'edit']);
        Route::post('peminjaman',                [PeminjamanController::class, 'store']);
        Route::get('peminjaman/{peminjaman}',     [PeminjamanController::class, 'show']);
        Route::put('peminjaman/{peminjaman}',     [PeminjamanController::class, 'update']);
        Route::delete('peminjaman/{peminjaman}',  [PeminjamanController::class, 'destroy']);
    });
});



