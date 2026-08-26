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

    public function iosProductId(): string
    {
        return config('price_tiers.bundle_id') . '.' . $this->tier_key;
    }

    public function androidProductId(): string
    {
        return $this->tier_key;
    }
}
