<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_sets', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('category_id')
                ->constrained('question_set_packages')->nullOnDelete();
            $table->boolean('trial_enabled')->default(false)->after('price_tier_id');
            $table->enum('trial_type', ['attempts', 'days'])->nullable()->after('trial_enabled');
            $table->unsignedInteger('trial_value')->nullable()->after('trial_type');
        });
    }

    public function down(): void
    {
        Schema::table('question_sets', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
            $table->dropColumn(['package_id', 'trial_enabled', 'trial_type', 'trial_value']);
        });
    }
};
