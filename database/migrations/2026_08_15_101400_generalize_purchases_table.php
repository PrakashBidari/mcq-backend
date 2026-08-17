<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('purchase_type', ['question_set', 'package', 'attempt_pack', 'subscription'])
                ->default('question_set')
                ->after('user_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['question_set_id']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('question_set_id')->nullable()->change();
            $table->foreignId('package_id')->nullable()->after('question_set_id')
                ->constrained('question_set_packages')->nullOnDelete();
            $table->foreignId('attempt_pack_id')->nullable()->after('package_id')
                ->constrained('attempt_packs')->nullOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->after('attempt_pack_id')
                ->constrained('subscription_plans')->nullOnDelete();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreign('question_set_id')->references('id')->on('question_sets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropForeign(['attempt_pack_id']);
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn(['purchase_type', 'package_id', 'attempt_pack_id', 'subscription_plan_id']);

            $table->dropForeign(['question_set_id']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('question_set_id')->nullable(false)->change();
            $table->foreign('question_set_id')->references('id')->on('question_sets')->cascadeOnDelete();
        });
    }
};
