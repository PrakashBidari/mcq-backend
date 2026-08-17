<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_key',
        'duration_days',
        'price',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function isLifetime(): bool
    {
        return is_null($this->duration_days);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function iosProductId(): string
    {
        return config('price_tiers.bundle_id') . '.' . $this->product_key;
    }

    public function androidProductId(): string
    {
        return $this->product_key;
    }
}
