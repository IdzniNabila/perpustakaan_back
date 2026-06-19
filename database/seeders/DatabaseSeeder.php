<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. User admin default (Bisa login & kelola data perpustakaan)
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@gmail.com',
            'password' => 'admin123',   // akan di-hash otomatis oleh cast 'hashed'
            'role'     => 'admin',
        ]);

        // 2. User pengunjung default (Bisa login & lihat data saja)
        User::create([
            'name'     => 'Pengunjung',
            'email'    => 'pengunjung@gmail.com',
            'password' => 'pengunjung123',
            'role'     => 'pengunjung',
        ]);

        // 5 Anggota Perpustakaan (Kecuali User admin, password = null)
        $members = [
            ['name' => 'Idzni Nabila',  'email' => 'idzni@gmail.com',   'password' => null, 'role' => 'pengunjung', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alfaridzi',     'email' => 'alfaridzi@gmail.com','password' => null, 'role' => 'pengunjung', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Budi Santoso',  'email' => 'budi@gmail.com',    'password' => null, 'role' => 'pengunjung', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Siti Aminah',   'email' => 'siti@gmail.com',    'password' => null, 'role' => 'pengunjung', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rian Hidayat',  'email' => 'rian@gmail.com',    'password' => null, 'role' => 'pengunjung', 'created_at' => now(), 'updated_at' => now()],
        ];
        User::insert($members);

        // 2. Data Buku (10 records)
        $books = [
            ['name' => 'Bahasa Inggris',       'description' => 'Buku pelajaran Bahasa Inggris',   'penerbit' => 'Penerbit A', 'tanggal_terbit' => '2019-12-24', 'stock' => 15, 'image' => '/covers/bahasa_inggris.png'],
            ['name' => 'Pemrograman PHP',      'description' => 'Panduan lengkap PHP modern',      'penerbit' => 'Penerbit B', 'tanggal_terbit' => '2020-01-01', 'stock' => 10, 'image' => '/covers/pemrograman_php.png'],
            ['name' => 'Laravel Mastery',      'description' => 'Kuasai Laravel dari nol',         'penerbit' => 'Penerbit C', 'tanggal_terbit' => '2021-06-15', 'stock' => 5,  'image' => '/covers/laravel_mastery.png'],
            ['name' => 'Algoritma Dasar',      'description' => 'Struktur data dan algoritma',     'penerbit' => 'Penerbit D', 'tanggal_terbit' => '2018-03-10', 'stock' => 8,  'image' => '/covers/algoritma_dasar.png'],
            ['name' => 'Dasar Database SQL',   'description' => 'Belajar SQL untuk pemula',        'penerbit' => 'Penerbit E', 'tanggal_terbit' => '2022-04-11', 'stock' => 12, 'image' => '/covers/dasar_database_sql.png'],
            ['name' => 'Pemrograman Javascript','description' => 'Eksplorasi modern JS (ES6+)',     'penerbit' => 'Penerbit F', 'tanggal_terbit' => '2021-11-20', 'stock' => 7,  'image' => '/covers/pemrograman_javascript.png'],
            ['name' => 'Keamanan Jaringan',    'description' => 'Pengenalan keamanan siber',       'penerbit' => 'Penerbit G', 'tanggal_terbit' => '2020-08-05', 'stock' => 6,  'image' => '/covers/keamanan_jaringan.png'],
            ['name' => 'Kecerdasan Buatan',    'description' => 'Dasar-dasar AI & Machine Learning','penerbit' => 'Penerbit H', 'tanggal_terbit' => '2023-01-15', 'stock' => 4,  'image' => '/covers/kecerdasan_buatan.png'],
            ['name' => 'Desain UI/UX',          'description' => 'Prinsip dasar desain antarmuka',  'penerbit' => 'Penerbit I', 'tanggal_terbit' => '2022-09-30', 'stock' => 0,  'image' => '/covers/desain_ui_ux.png'],
            ['name' => 'Arsitektur Software',  'description' => 'Pola desain arsitektur modern',   'penerbit' => 'Penerbit J', 'tanggal_terbit' => '2023-05-18', 'stock' => 0,  'image' => '/covers/arsitektur_software.png'],
        ];

        foreach ($books as $b) {
            Book::create(array_merge($b, ['created_at' => now(), 'updated_at' => now()]));
        }

        // 3. Data Peminjaman (6 records)
        // Menghubungkan sebagian buku dengan anggota untuk membedakan tab filter
        $peminjaman = [
            ['book_id' => 1,  'user_id' => 2, 'tgl_pinjam' => '2026-06-01', 'tgl_kembali' => '2026-06-08', 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 2,  'user_id' => 3, 'tgl_pinjam' => '2026-06-02', 'tgl_kembali' => '2026-06-09', 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 3,  'user_id' => 4, 'tgl_pinjam' => '2026-06-03', 'tgl_kembali' => '2026-06-10', 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 4,  'user_id' => 5, 'tgl_pinjam' => '2026-06-04', 'tgl_kembali' => '2026-06-11', 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 5,  'user_id' => 6, 'tgl_pinjam' => '2026-06-05', 'tgl_kembali' => '2026-06-12', 'created_at' => now(), 'updated_at' => now()],
            ['book_id' => 10, 'user_id' => 2, 'tgl_pinjam' => '2026-06-10', 'tgl_kembali' => '2026-06-17', 'created_at' => now(), 'updated_at' => now()],
        ];

        Peminjaman::insert($peminjaman);
    }
}
