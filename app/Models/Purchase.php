<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'purchase_type',
        'question_set_id',
        'package_id',
        'attempt_pack_id',
        'subscription_plan_id',
        'platform',
        'product_id',
        'price_tier',
        'transaction_id',
        'price_paid',
        'currency',
        'status',
        'purchased_at',
        'access_type',
        'access_value',
        'attempts_used',
        'expires_at',
    ];

    protected $casts = [
        'price_paid'    => 'decimal:2',
        'purchased_at'  => 'datetime',
        'access_value'  => 'integer',
        'attempts_used' => 'integer',
        'expires_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function package()
    {
        return $this->belongsTo(QuestionSetPackage::class, 'package_id');
    }

    public function attemptPack()
    {
        return $this->belongsTo(AttemptPack::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public static function userOwns(int $userId, int $questionSetId): bool
    {
        return self::activeQuestionSetPurchase($userId, $questionSetId) !== null;
    }

    public static function userOwnsPackage(int $userId, int $packageId): bool
    {
        return self::activePackagePurchase($userId, $packageId) !== null;
    }

    // A purchase no longer means permanent ownership - it grants a fixed number of
    // attempts or a fixed number of days, snapshotted onto the purchase row itself.
    // This finds the most recent one that still has access left, if any.
    public static function activeQuestionSetPurchase(int $userId, int $questionSetId): ?self
    {
        return self::where('user_id', $userId)
            ->where('question_set_id', $questionSetId)
            ->where('purchase_type', 'question_set')
            ->where('status', 'completed')
            ->orderByDesc('id')
            ->get()
            ->first(fn (self $purchase) => $purchase->isActive());
    }

    public static function activePackagePurchase(int $userId, int $packageId): ?self
    {
        return self::where('user_id', $userId)
            ->where('package_id', $packageId)
            ->where('purchase_type', 'package')
            ->where('status', 'completed')
            ->orderByDesc('id')
            ->get()
            ->first(fn (self $purchase) => $purchase->isActive());
    }

    public function isActive(): bool
    {
        if ($this->expires_at) {
            return now()->lt($this->expires_at);
        }

        if ($this->access_type === 'attempts') {
            return $this->attempts_used < $this->access_value;
        }

        // Purchases made before access grants existed - treat as permanent (legacy).
        return true;
    }
}
