<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Removes the price tiers that predate the current 100/200/300/400/500/1000/1500/
    // 2000/3000/5000 catalog: the original 800/1200/1800/2800 set, plus 116/120/255
    // which were auto-created by the old free-typed-price admin form (never matched a
    // real App Store Connect / Google Play product). Any question set/package still
    // pointing at one of these is reassigned to the nearest tier in the current catalog
    // first, since price_tier_id is restrictOnDelete.
    public function up(): void
    {
        $legacyKeys = ['tier_800', 'tier_1200', 'tier_1800', 'tier_2800', 'tier_116', 'tier_120', 'tier_255'];

        $currentTiers = DB::table('price_tiers')
            ->whereNotIn('tier_key', $legacyKeys)
            ->orderBy('amount')
            ->get(['id', 'amount']);

        $legacyTiers = DB::table('price_tiers')->whereIn('tier_key', $legacyKeys)->get(['id', 'amount']);

        foreach ($legacyTiers as $legacy) {
            $nearest = $currentTiers->sortBy(fn ($t) => abs($t->amount - $legacy->amount))->first();
            if (!$nearest) {
                continue;
            }

            DB::table('question_sets')->where('price_tier_id', $legacy->id)->update([
                'price_tier_id' => $nearest->id,
                'price'         => $nearest->amount,
            ]);

            DB::table('question_set_packages')->where('price_tier_id', $legacy->id)->update([
                'price_tier_id' => $nearest->id,
            ]);
        }

        DB::table('price_tiers')->whereIn('tier_key', $legacyKeys)->delete();

        // Renumber sort_order to a clean 1..N sequence over what's left.
        $remaining = DB::table('price_tiers')->orderBy('sort_order')->get(['id']);
        foreach ($remaining as $i => $tier) {
            DB::table('price_tiers')->where('id', $tier->id)->update(['sort_order' => $i + 1]);
        }
    }

    public function down(): void
    {
        // Data-only cleanup migration - the removed tiers were leftovers, not
        // reproducible on rollback. No-op.
    }
};
