<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptPack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_key',
        'attempts_count',
        'price',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'attempts_count' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
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
