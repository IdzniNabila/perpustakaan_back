<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rak_bukus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['tersimpan', 'sedang_dibaca', 'selesai'])->default('tersimpan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rak_bukus');
    }
};
