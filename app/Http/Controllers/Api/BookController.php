<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * GET /api/book
     * Daftar semua buku (dengan pencarian opsional).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Book::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%");
            });
        }

        $books = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data'    => $books,
        ]);
    }

    /**
     * POST /api/book
     * Tambah buku baru.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:191'],
            'description'    => ['required', 'string', 'max:500'],
            'penerbit'       => ['required', 'string', 'max:191'],
            'tanggal_terbit' => ['required', 'date'],
            'stock'          => ['required', 'integer', 'min:0'],
            'image'          => ['nullable', 'string'],
        ]);

        $book = Book::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil ditambahkan.',
            'data'    => $book,
        ], 201);
    }

    /**
     * GET /api/book/{id}
     * Detail satu buku beserta data peminjaman aktif.
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $book,
        ]);
    }

    /**
     * GET /api/book/{id}/edit
     * Data buku untuk form edit (alias show).
     */
    public function edit(Book $book): JsonResponse
    {
        return $this->show($book);
    }

    /**
     * PUT/PATCH /api/book/{id}
     * Perbarui data buku.
     */
    public function update(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:191'],
            'description'    => ['required', 'string', 'max:500'],
            'penerbit'       => ['required', 'string', 'max:191'],
            'tanggal_terbit' => ['required', 'date'],
            'stock'          => ['required', 'integer', 'min:0'],
            'image'          => ['nullable', 'string'],
        ]);

        $book->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data buku berhasil diperbarui.',
            'data'    => $book->fresh(),
        ]);
    }

    /**
     * DELETE /api/book/{id}
     * Hapus buku (cek dulu apakah sedang dipinjam).
     */
    public function destroy(Book $book): JsonResponse
    {
        $sedangDipinjam = $book->peminjaman()->count() > 0;

        if ($sedangDipinjam) {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak bisa dihapus karena masih ada riwayat peminjaman.',
            ], 409);
        }

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dihapus.',
        ]);
    }
}
