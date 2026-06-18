<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';

    protected $fillable = [
        'book_id',
        'user_id',
        'tgl_pinjam',
        'tgl_kembali',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pinjam'  => 'date',
            'tgl_kembali' => 'date',
        ];
    }

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
