<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('ol_key')->unique()->nullable();        // Open Library key: "/works/OL45883W"
            $table->string('google_books_id')->unique()->nullable(); // Fallback
            $table->string('title');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('ia_identifier')->nullable(); // Internet Archive ID untuk PDF embed
            $table->string('pdf_url')->nullable();       // Direct PDF URL jika tersedia
            $table->integer('year')->nullable();
            $table->string('subject')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
