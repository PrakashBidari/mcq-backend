<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_sets', function (Blueprint $table) {
            $table->foreignId('price_tier_id')->nullable()->after('price_tier')
                ->constrained('price_tiers')->restrictOnDelete();
        });

        // Backfill from the legacy string column. The old `price_tier` column is kept
        // live (dual-read/dual-write) until the app code is fully cut over.
        DB::statement(
            'UPDATE question_sets qs
             JOIN price_tiers pt ON pt.tier_key = qs.price_tier
             SET qs.price_tier_id = pt.id
             WHERE qs.price_tier IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::table('question_sets', function (Blueprint $table) {
            $table->dropForeign(['price_tier_id']);
            $table->dropColumn('price_tier_id');
        });
    }
};
