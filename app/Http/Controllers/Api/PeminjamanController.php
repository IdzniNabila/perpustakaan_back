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
        $user = auth()->user();
        $query = Peminjaman::with(['book', 'user']);

        // Jika bukan admin, hanya bisa melihat peminjaman miliknya sendiri
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        // Hanya tampilkan peminjaman aktif ('dipinjam') agar sinkron dengan UI frontend
        $query->where('status', 'dipinjam');

        $peminjaman = $query->orderByDesc('created_at')->get();

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
        $user = auth()->user();

        // Admin bisa menentukan user_id lain, pengunjung dipaksa ke id miliknya sendiri
        if ($user->role !== 'admin') {
            $request->merge(['user_id' => $user->id]);
        }

        $data = $request->validate([
            'book_id'     => ['required', 'exists:books,id'],
            'user_id'     => ['required', 'exists:users,id'],
            'tgl_pinjam'  => ['required', 'date'],
            'tgl_kembali' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
        ]);

        // Default status adalah dipinjam, tgl_pengembalian null, denda 0
        $data['status'] = 'dipinjam';
        $data['tgl_pengembalian'] = null;
        $data['denda'] = 0;

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
            // Naikkan stok buku kembali
            Book::where('id', $peminjaman->book_id)->increment('stock');

            // Hitung denda jika ada (keterlambatan pengembalian)
            $tglKembali = $peminjaman->tgl_kembali;
            $now = now()->startOfDay();
            $denda = 0;

            if ($now->greaterThan($tglKembali)) {
                $durasiTerlambat = $now->diffInDays($tglKembali);
                $denda = $durasiTerlambat * 500;
            }

            // Ubah status dan simpan histori daripada menghapus data dari database
            $peminjaman->update([
                'status'           => 'kembali',
                'tgl_pengembalian' => now(),
                'denda'            => $denda,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dikembalikan.',
        ]);
    }

    /**
     * POST /api/peminjaman/{peminjaman}/return
     * Kembalikan buku secara eksplisit (Endpoint Baru).
     */
    public function returnBook(Peminjaman $peminjaman): JsonResponse
    {
        if ($peminjaman->status === 'kembali') {
            return response()->json([
                'success' => false,
                'message' => 'Buku sudah dikembalikan sebelumnya.',
            ], 400);
        }

        DB::transaction(function () use ($peminjaman) {
            Book::where('id', $peminjaman->book_id)->increment('stock');

            $tglKembali = $peminjaman->tgl_kembali;
            $now = now()->startOfDay();
            $denda = 0;

            if ($now->greaterThan($tglKembali)) {
                $durasiTerlambat = $now->diffInDays($tglKembali);
                $denda = $durasiTerlambat * 500;
            }

            $peminjaman->update([
                'status'           => 'kembali',
                'tgl_pengembalian' => now(),
                'denda'            => $denda,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dikembalikan.',
            'data'    => $peminjaman->fresh(['book', 'user']),
        ]);
    }

    /**
     * GET /api/my-history
     * Mengembalikan riwayat peminjaman (aktif & selesai) untuk pengunjung yang login.
     */
    public function myHistory(): JsonResponse
    {
        $user = auth()->user();

        $history = Peminjaman::with(['book', 'user'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $history,
        ]);
    }
    public function extendBorrowing(Request $request, Peminjaman $peminjaman): JsonResponse
    {
        $user = auth()->user();

        // Hanya peminjam atau admin yang bisa memperpanjang
        if ($user->role !== 'admin' && $peminjaman->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($peminjaman->status === 'kembali') {
            return response()->json(['success' => false, 'message' => 'Buku sudah dikembalikan.'], 400);
        }

        // Batasi perpanjangan maksimal 2 kali
        if ($peminjaman->diperpanjang_sebanyak >= 2) {
            return response()->json(['success' => false, 'message' => 'Batas maksimal perpanjangan telah tercapai.'], 400);
        }

        // Tambah 7 hari dari tanggal kembali saat ini
        $newTglKembali = \Carbon\Carbon::parse($peminjaman->tgl_kembali)->addDays(7);

        $peminjaman->update([
            'tgl_kembali' => $newTglKembali,
            'diperpanjang_sebanyak' => $peminjaman->diperpanjang_sebanyak + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Masa pinjam berhasil diperpanjang 7 hari.',
            'data'    => $peminjaman->fresh(['book', 'user']),
        ]);
    }
}
