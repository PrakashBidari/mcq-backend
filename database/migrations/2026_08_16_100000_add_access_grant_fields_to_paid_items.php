<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // What a paid purchase of this item actually grants: N attempts or N days -
        // there is no more "buy once, own forever" for individually-priced items.
        Schema::table('question_sets', function (Blueprint $table) {
            $table->enum('access_type', ['attempts', 'days'])->nullable()->after('price_tier_id');
            $table->unsignedInteger('access_value')->nullable()->after('access_type');
        });

        Schema::table('question_set_packages', function (Blueprint $table) {
            $table->enum('access_type', ['attempts', 'days'])->nullable()->after('price_tier_id');
            $table->unsignedInteger('access_value')->nullable()->after('access_type');
        });

        // Snapshotted onto the purchase itself at verify-time so later admin edits to the
        // item don't retroactively change what a customer already paid for.
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('access_type', ['attempts', 'days'])->nullable()->after('price_tier');
            $table->unsignedInteger('access_value')->nullable()->after('access_type');
            $table->unsignedInteger('attempts_used')->default(0)->after('access_value');
            $table->timestamp('expires_at')->nullable()->after('attempts_used');
        });
    }

    public function down(): void
    {
        Schema::table('question_sets', function (Blueprint $table) {
            $table->dropColumn(['access_type', 'access_value']);
        });

        Schema::table('question_set_packages', function (Blueprint $table) {
            $table->dropColumn(['access_type', 'access_value']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['access_type', 'access_value', 'attempts_used', 'expires_at']);
        });
    }
};
