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

    public function kategoris()
    {
        return $this->belongsToMany(Kategori::class, 'book_kategori', 'book_id', 'kategori_id');
    }

    public function ulasans()
    {
        return $this->hasMany(Ulasan::class);
    }

    public function rakBukus()
    {
        return $this->hasMany(RakBuku::class);
    }

    // ── Scope ───────────────────────────────────────────────────────────────

    /** Filter buku yang masih ada stok */
    public function scopeAvailable($query)
    {
        return $query->where('stock', '>', 0);
    }
}
