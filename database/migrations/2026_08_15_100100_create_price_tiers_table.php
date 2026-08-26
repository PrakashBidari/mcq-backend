<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('tier_key')->unique();
            $table->string('label')->nullable();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('JPY');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed with the exact tiers/amounts already used by config/price_tiers.php so
        // existing App Store Connect / Google Play Console products keep resolving.
        $now = now();
        $tiers = [
            'tier_100'  => 100,
            'tier_200'  => 200,
            'tier_300'  => 300,
            'tier_400'  => 400,
            'tier_500'  => 500,
            'tier_1000' => 1000,
            'tier_1500' => 1500,
            'tier_2000' => 2000,
            'tier_3000' => 3000,
            'tier_5000' => 5000,
        ];

        $rows = [];
        $order = 1;
        foreach ($tiers as $key => $amount) {
            $rows[] = [
                'tier_key' => $key,
                'label' => '¥' . number_format($amount),
                'amount' => $amount,
                'currency' => 'JPY',
                'is_active' => true,
                'sort_order' => $order++,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('price_tiers')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('price_tiers');
    }
};
