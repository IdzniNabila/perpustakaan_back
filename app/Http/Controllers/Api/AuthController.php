<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Login — bandingkan password hashed, kembalikan JWT token.
     *
     * POST /api/auth/login
     * Body: { email, password }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Bandingkan password menggunakan Hash::check
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Generate JWT token manual dari user
        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token);
    }

    /**
     * Logout — blacklist token saat ini.
     *
     * POST /api/auth/logout
     */
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Refresh — perbarui token yang hampir expired.
     *
     * POST /api/auth/refresh
     */
    public function refresh(): JsonResponse
    {
        $token = Auth::guard('api')->refresh();
        return $this->respondWithToken($token);
    }

    /**
     * Me — data user yang sedang login.
     *
     * GET /api/auth/me
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => Auth::guard('api')->user(),
        ]);
    }

    /**
     * Register user baru (password di-hash otomatis oleh model).
     *
     * POST /api/auth/signup
     */
    public function signup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Password di-hash secara otomatis oleh cast 'hashed' pada model User
        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dibuat.',
            'data'    => $user,
        ], 201);
    }

    /**
     * Update profile — perbarui nama, email, dan password user yang sedang login.
     *
     * PUT /api/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan atau belum login.',
            ], 401);
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', "unique:users,email,{$user->id}"],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $user->only('id', 'name', 'email', 'role'),
        ]);
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
        ]);
    }
}
