<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Prices were originally whole-yen integers (JPY has no decimal subunit in normal
// use), but the admin now wants freeform decimal pricing - widen the three
// integer-backed price columns to decimal(10,2) so a typed value like 116.50
// survives instead of being silently truncated to 116 by the column type.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_tiers', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->unsigned()->change();
        });

        Schema::table('attempt_packs', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->unsigned()->change();
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->unsigned()->change();
        });
    }

    public function down(): void
    {
        Schema::table('price_tiers', function (Blueprint $table) {
            $table->unsignedInteger('amount')->change();
        });

        Schema::table('attempt_packs', function (Blueprint $table) {
            $table->unsignedInteger('price')->change();
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('price')->change();
        });
    }
};
