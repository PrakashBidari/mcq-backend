<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_set_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_paid')->default(false);
            $table->foreignId('price_tier_id')->nullable()->constrained('price_tiers')->restrictOnDelete();
            $table->boolean('trial_enabled')->default(false);
            $table->enum('trial_type', ['attempts', 'days'])->nullable();
            $table->unsignedInteger('trial_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_set_packages');
    }
};
