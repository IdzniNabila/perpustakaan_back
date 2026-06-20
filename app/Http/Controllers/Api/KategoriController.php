<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KategoriController extends Controller
{
    public function index(): JsonResponse
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();
        return response()->json([
            'success' => true,
            'data'    => $kategori,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
        ]);

        $kategori = Kategori::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.',
            'data'    => $kategori,
        ], 201);
    }

    public function show(Kategori $kategori): JsonResponse
    {
        // Load buku-buku dalam kategori ini
        $kategori->load('books');
        return response()->json([
            'success' => true,
            'data'    => $kategori,
        ]);
    }

    public function update(Request $request, Kategori $kategori): JsonResponse
    {
        $data = $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
        ]);

        $kategori->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => $kategori,
        ]);
    }

    public function destroy(Kategori $kategori): JsonResponse
    {
        $kategori->delete();
        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
