<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_set_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['ios', 'android']);
            $table->string('product_id');
            $table->string('price_tier');
            $table->string('transaction_id')->unique();
            $table->decimal('price_paid', 8, 2)->nullable();
            $table->string('currency', 3)->default('JPY');
            $table->enum('status', ['pending', 'completed', 'refunded', 'failed'])->default('pending');
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'question_set_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
