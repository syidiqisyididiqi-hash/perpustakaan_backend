<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('isbn', 100)->unique();
            $table->string('title', 150);
            $table->string('author', 100);
            $table->string('publisher', 100);
            $table->year('published_year');
            $table->unsignedInteger('stock');
            $table->string('rack_code', 20);
            $table->string('cover', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
