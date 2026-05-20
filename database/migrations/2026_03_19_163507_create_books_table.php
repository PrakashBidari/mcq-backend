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
            $table->string('title');
            $table->string('author');
            $table->text('description')->nullable();
            $table->string('cover'); // URL to cover image
            $table->string('image')->nullable();
            $table->decimal('rating', 2, 1)->default(0); // 0.0 to 9.9
            $table->integer('pages');
            $table->string('duration'); // e.g., "12h 30m"
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->enum('difficulty', ['Beginner', 'Intermediate', 'Advanced']);
            $table->integer('students')->default(0); // Number of students/readers
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
