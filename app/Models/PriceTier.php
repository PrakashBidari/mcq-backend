<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tier_key',
        'label',
        'amount',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function questionSets()
    {
        return $this->hasMany(QuestionSet::class);
    }

    public function packages()
    {
        return $this->hasMany(QuestionSetPackage::class);
    }

    // Admin types a plain yen amount on the question-set/package form (no more picking
    // from a preset list) - this resolves it to a reusable tier row, creating one if no
    // tier at this exact amount exists yet. Store products are still mapped 1:1 by
    // tier_key, so a brand-new amount still needs a matching product created manually in
    // App Store Connect / Google Play Console before it's purchasable.
    //
    // Amounts can be decimal (e.g. 116.50) - the tier_key can't contain a literal "."
    // (used as a literal Android product ID / iOS product ID suffix), so a fractional
    // part is rendered as "_50" instead: 116.5 -> tier_116_5, 116 -> tier_116 (unchanged
    // from the old whole-yen-only scheme, so existing tiers still resolve the same way).
    public static function forAmount(float $amount): self
    {
        $amount = round($amount, 2);
        $trimmed = rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
        $tierKey = 'tier_' . str_replace('.', '_', $trimmed);

        return self::firstOrCreate(
            ['tier_key' => $tierKey],
            [
                'label'      => '¥' . number_format($amount, $amount == floor($amount) ? 0 : 2),
                'amount'     => $amount,
                'currency'   => 'JPY',
                'is_active'  => true,
                'sort_order' => 0,
            ]
        );
    }

    public function iosProductId(): string
    {
        return config('price_tiers.bundle_id') . '.' . $this->tier_key;
    }

    public function androidProductId(): string
    {
        return $this->tier_key;
    }
}
