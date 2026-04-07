// database/migrations/xxxx_create_user_books_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['wishlist', 'reading', 'finished'])->default('wishlist');
            $table->timestamp('started_reading_at')->nullable(); // Auto-set saat PDF dibuka
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'book_id']); // Satu user, satu status per buku
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_books');
    }
};
