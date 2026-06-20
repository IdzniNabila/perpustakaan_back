<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RakBuku;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RakBukuController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $rakBuku = RakBuku::with('book')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $rakBuku,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $data = $request->validate([
            'book_id' => 'required|exists:books,id',
            'status'  => 'nullable|in:tersimpan,sedang_dibaca,selesai',
        ]);

        $rakBuku = RakBuku::firstOrCreate(
            ['user_id' => $user->id, 'book_id' => $data['book_id']],
            ['status' => $data['status'] ?? 'tersimpan']
        );

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil ditambahkan ke rak.',
            'data'    => $rakBuku->load('book'),
        ], 201);
    }

    public function destroy(RakBuku $rakBuku): JsonResponse
    {
        $user = auth()->user();

        if ($rakBuku->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $rakBuku->delete();

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dihapus dari rak.',
        ]);
    }
}
