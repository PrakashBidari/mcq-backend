<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Brings environments that already ran the original price_tiers seed (300/500/800/
    // 1200/1800/2800) up to the current catalog (100/200/300/400/500/1000/1500/2000/
    // 3000/5000). Existing tiers (300, 500) are re-labelled/reordered rather than
    // duplicated; tiers no longer in the list (800, 1200, 1800, 2800) are left in place
    // - they're restrictOnDelete-protected if any question set/package still uses one,
    // and can be deactivated from the admin Price Tiers screen once nothing references them.
    public function up(): void
    {
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

        $order = 1;
        foreach ($tiers as $key => $amount) {
            $attributes = [
                'label'      => '¥' . number_format($amount),
                'amount'     => $amount,
                'currency'   => 'JPY',
                'is_active'  => true,
                'sort_order' => $order,
                'updated_at' => $now,
            ];

            $existing = DB::table('price_tiers')->where('tier_key', $key)->first();
            if ($existing) {
                DB::table('price_tiers')->where('tier_key', $key)->update($attributes);
            } else {
                DB::table('price_tiers')->insert(array_merge($attributes, [
                    'tier_key'   => $key,
                    'created_at' => $now,
                ]));
            }

            $order++;
        }

        // Push any pre-existing tiers outside this list (e.g. tier_800, tier_1200) to the
        // end of the list, ordered by amount, instead of deactivating them outright.
        $legacyTiers = DB::table('price_tiers')
            ->whereNotIn('tier_key', array_keys($tiers))
            ->orderBy('amount')
            ->get();

        foreach ($legacyTiers as $tier) {
            DB::table('price_tiers')->where('id', $tier->id)->update(['sort_order' => $order]);
            $order++;
        }
    }

    public function down(): void
    {
        // Data-only migration - no schema to roll back, and reverting the catalog
        // would risk breaking already-purchased tiers. No-op.
    }
};
