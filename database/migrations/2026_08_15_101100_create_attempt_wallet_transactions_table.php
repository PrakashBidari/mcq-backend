<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Auditable ledger — the sole source of truth for a user's attempt-wallet
        // balance (sum of `delta`). No separate mutable counter column, so a
        // corrupted/failed write can never silently desync balance from history.
        Schema::create('attempt_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('delta');
            $table->enum('reason', ['purchase', 'trial_grant', 'consume', 'admin_adjustment']);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->integer('balance_after');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempt_wallet_transactions');
    }
};
