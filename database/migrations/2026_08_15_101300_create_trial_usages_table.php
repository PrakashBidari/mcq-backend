<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('trialable'); // trialable_type, trialable_id (question_set or package)
            $table->unsignedInteger('attempts_used')->default(0);
            $table->timestamp('first_used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'trialable_type', 'trialable_id'], 'trial_usages_user_trialable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_usages');
    }
};
