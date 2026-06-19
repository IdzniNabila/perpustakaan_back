<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'penerbit',
        'tanggal_terbit',
        'stock',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date',
            'stock'          => 'integer',
        ];
    }

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class);
    }

    // ── Scope ───────────────────────────────────────────────────────────────

    /** Filter buku yang masih ada stok */
    public function scopeAvailable($query)
    {
        return $query->where('stock', '>', 0);
    }
}
