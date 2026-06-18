<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    /**
     * GET /api/peminjaman
     * Daftar semua peminjaman beserta relasi buku dan user.
     */
    public function index(): JsonResponse
    {
        $peminjaman = Peminjaman::with(['book', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $peminjaman,
        ]);
    }

    /**
     * GET /api/peminjaman/create
     * Data awal untuk form tambah peminjaman (daftar buku & user).
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'users' => User::select('id', 'name')->orderBy('name')->get(),
                'books' => Book::select('id', 'name', 'stock')->available()->orderBy('name')->get(),
            ],
        ]);
    }

    /**
     * POST /api/peminjaman
     * Catat peminjaman baru — kurangi stok buku secara atomic.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id'     => ['required', 'exists:books,id'],
            'user_id'     => ['required', 'exists:users,id'],
            'tgl_pinjam'  => ['required', 'date'],
            'tgl_kembali' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
        ]);

        // Gunakan DB transaction agar stok & record selalu konsisten
        $peminjaman = DB::transaction(function () use ($data) {
            $book = Book::lockForUpdate()->findOrFail($data['book_id']);

            if ($book->stock <= 0) {
                abort(422, 'Stok buku habis, tidak bisa dipinjam.');
            }

            $book->decrement('stock');

            return Peminjaman::create($data);
        });

        $peminjaman->load(['book', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil dicatat.',
            'data'    => $peminjaman,
        ], 201);
    }

    /**
     * GET /api/peminjaman/{id}/edit
     * Data peminjaman + daftar buku & user untuk form edit.
     */
    public function edit(Peminjaman $peminjaman): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'peminjaman' => $peminjaman->load(['book', 'user']),
                'users'      => User::select('id', 'name')->orderBy('name')->get(),
                'books'      => Book::select('id', 'name', 'stock')->orderBy('name')->get(),
            ],
        ]);
    }

    /**
     * PUT/PATCH /api/peminjaman/{id}
     * Update peminjaman — kelola stok dengan benar (bug versi lama sudah diperbaiki).
     *
     * Bug lama: jika buku yang dipilih sama, stok langsung dikurangi tanpa
     * ditambah dulu, sehingga stok terus berkurang setiap kali di-update.
     * Perbaikan: kembalikan stok buku lama dulu, baru kurangi buku baru.
     */
    public function update(Request $request, Peminjaman $peminjaman): JsonResponse
    {
        $data = $request->validate([
            'book_id'     => ['required', 'exists:books,id'],
            'user_id'     => ['required', 'exists:users,id'],
            'tgl_pinjam'  => ['required', 'date'],
            'tgl_kembali' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
        ]);

        DB::transaction(function () use ($data, $peminjaman) {
            $oldBookId = $peminjaman->book_id;
            $newBookId = (int) $data['book_id'];

            if ($oldBookId !== $newBookId) {
                // Buku diganti: kembalikan stok buku lama, kurangi stok buku baru
                Book::where('id', $oldBookId)->increment('stock');

                $newBook = Book::lockForUpdate()->findOrFail($newBookId);
                if ($newBook->stock <= 0) {
                    abort(422, 'Stok buku baru habis.');
                }
                $newBook->decrement('stock');
            }
            // Jika buku sama, stok tidak berubah — ini adalah bug-fix dari versi lama

            $peminjaman->update($data);
        });

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil diperbarui.',
            'data'    => $peminjaman->fresh(['book', 'user']),
        ]);
    }

    /**
     * DELETE /api/peminjaman/{id}
     * Hapus record peminjaman — kembalikan stok buku.
     */
    public function destroy(Peminjaman $peminjaman): JsonResponse
    {
        DB::transaction(function () use ($peminjaman) {
            Book::where('id', $peminjaman->book_id)->increment('stock');
            $peminjaman->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Data peminjaman berhasil dihapus.',
        ]);
    }
}
