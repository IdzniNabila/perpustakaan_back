<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UlasanController extends Controller
{
    public function index(Book $book): JsonResponse
    {
        $ulasan = Ulasan::with('user:id,name')
            ->where('book_id', $book->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $ulasan,
        ]);
    }

    public function store(Request $request, Book $book): JsonResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string',
        ]);

        // Cek jika sudah pernah memberikan ulasan
        $ulasan = Ulasan::updateOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id],
            ['rating' => $data['rating'], 'komentar' => $data['komentar']]
        );

        return response()->json([
            'success' => true,
            'message' => 'Ulasan berhasil disimpan.',
            'data'    => $ulasan->load('user:id,name'),
        ], 201);
    }
}
