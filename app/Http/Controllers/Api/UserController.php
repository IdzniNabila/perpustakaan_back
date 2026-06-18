<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * GET /api/user
     * Daftar semua user.
     */
    public function index(): JsonResponse
    {
        $users = User::select('id', 'name', 'email', 'created_at')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }

    /**
     * POST /api/user
     * Buat user/anggota baru.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['nullable', Password::min(6)],
        ]);

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan.',
            'data'    => $user->only('id', 'name', 'email', 'created_at'),
        ], 201);
    }

    /**
     * GET /api/user/{id}
     * Detail user beserta histori peminjaman.
     */
    public function show(User $user): JsonResponse
    {
        $user->load('peminjaman.book');

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    /**
     * GET /api/user/{id}/edit
     * Data user untuk form edit.
     */
    public function edit(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $user->only('id', 'name', 'email'),
        ]);
    }

    /**
     * PUT/PATCH /api/user/{id}
     * Perbarui data user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', "unique:users,email,{$user->id}"],
            'password' => ['nullable', Password::min(6)],
        ]);

        // Jika password dikosongkan, jangan timpa yang lama
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diperbarui.',
            'data'    => $user->fresh(['id', 'name', 'email']),
        ]);
    }

    /**
     * DELETE /api/user/{id}
     * Hapus user (cek apakah masih ada peminjaman aktif).
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->peminjaman()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak bisa dihapus karena masih ada riwayat peminjaman.',
            ], 409);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.',
        ]);
    }

    /**
     * GET /api/auth/me (dipanggil dari AuthController)
     * Alias: data user yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ]);
    }
}
