<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User admin default
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@email.com',
            'password' => 'password',   // akan di-hash otomatis oleh cast 'hashed'
        ]);

        // Beberapa user anggota contoh
        User::insert([
            ['name' => 'English', 'email' => 'asd@email.com',   'password' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Works',   'email' => 'works@email.com', 'password' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Data buku contoh
        $books = [
            ['name' => 'Bahasa Inggris',   'description' => 'Buku pelajaran Bahasa Inggris', 'penerbit' => 'Penerbit A', 'tanggal_terbit' => '2019-12-24', 'stock' => 17],
            ['name' => 'Pemrograman PHP',  'description' => 'Panduan lengkap PHP modern',    'penerbit' => 'Penerbit B', 'tanggal_terbit' => '2020-01-01', 'stock' => 10],
            ['name' => 'Laravel Mastery',  'description' => 'Kuasai Laravel dari nol',       'penerbit' => 'Penerbit C', 'tanggal_terbit' => '2021-06-15', 'stock' => 5],
            ['name' => 'Algoritma Dasar',  'description' => 'Struktur data dan algoritma',   'penerbit' => 'Penerbit D', 'tanggal_terbit' => '2018-03-10', 'stock' => 8],
        ];

        foreach ($books as $b) {
            Book::create(array_merge($b, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
}
