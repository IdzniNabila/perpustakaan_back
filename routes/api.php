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

    // Peminjaman
    Route::get('peminjaman', [PeminjamanController::class, 'index']);
    Route::post('peminjaman', [PeminjamanController::class, 'store']); // Bisa dipanggil admin atau pengunjung
    Route::get('my-history', [PeminjamanController::class, 'myHistory']); // Riwayat peminjaman pengunjung sendiri

    // Perpanjangan peminjaman
    Route::post('peminjaman/{peminjaman}/extend', [PeminjamanController::class, 'extendBorrowing']);

    // e-Pustaka / Kategori (Read Only for public)
    Route::get('kategori', [\App\Http\Controllers\Api\KategoriController::class, 'index']);
    Route::get('kategori/{kategori}', [\App\Http\Controllers\Api\KategoriController::class, 'show']);

    // Ulasan
    Route::get('book/{book}/ulasan', [\App\Http\Controllers\Api\UlasanController::class, 'index']);
    Route::post('book/{book}/ulasan', [\App\Http\Controllers\Api\UlasanController::class, 'store']);

    // Rak Buku
    Route::get('rak-buku', [\App\Http\Controllers\Api\RakBukuController::class, 'index']);
    Route::post('rak-buku', [\App\Http\Controllers\Api\RakBukuController::class, 'store']);
    Route::delete('rak-buku/{rakBuku}', [\App\Http\Controllers\Api\RakBukuController::class, 'destroy']);

    // Update Profil Sendiri
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);

    Route::middleware('role:admin')->group(function () {
        Route::get('book/{book}/edit', [BookController::class, 'edit']);
        Route::post('book', [BookController::class, 'store']);
        Route::put('book/{book}', [BookController::class, 'update']);
        Route::delete('book/{book}', [BookController::class, 'destroy']);

        // CRUD Kategori khusus admin
        Route::post('kategori', [\App\Http\Controllers\Api\KategoriController::class, 'store']);
        Route::put('kategori/{kategori}', [\App\Http\Controllers\Api\KategoriController::class, 'update']);
        Route::delete('kategori/{kategori}', [\App\Http\Controllers\Api\KategoriController::class, 'destroy']);

        // User / Anggota
        Route::get('user/{user}/edit', [UserController::class, 'edit']);
        Route::apiResource('user', UserController::class);

        // Peminjaman (admin-only actions)
        Route::get('peminjaman/create',          [PeminjamanController::class, 'create']);
        Route::get('peminjaman/{peminjaman}/edit', [PeminjamanController::class, 'edit']);
        Route::get('peminjaman/{peminjaman}',     [PeminjamanController::class, 'show']);
        Route::put('peminjaman/{peminjaman}',     [PeminjamanController::class, 'update']);
        Route::delete('peminjaman/{peminjaman}',  [PeminjamanController::class, 'destroy']); // DELETE maps to return logic
        Route::post('peminjaman/{peminjaman}/return', [PeminjamanController::class, 'returnBook']); // Dedicated return route
    });
});



