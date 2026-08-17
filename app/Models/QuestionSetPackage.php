<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionSetPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'subcategory_id',
        'description',
        'is_active',
        'is_paid',
        'price_tier_id',
        'trial_enabled',
        'trial_type',
        'trial_value',
        'access_type',
        'access_value',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_paid' => 'boolean',
        'trial_enabled' => 'boolean',
        'trial_value' => 'integer',
        'access_value' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function priceTier()
    {
        return $this->belongsTo(PriceTier::class);
    }

    public function questionSets()
    {
        return $this->hasMany(QuestionSet::class, 'package_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'package_id');
    }

    public function isOwnedBy(?int $userId): bool
    {
        if (!$this->is_paid) {
            return true;
        }

        if (!$userId) {
            return false;
        }

        return Purchase::userOwnsPackage($userId, $this->id);
    }
}
