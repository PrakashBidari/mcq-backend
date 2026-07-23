<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_set_id',
        'platform',
        'product_id',
        'price_tier',
        'transaction_id',
        'price_paid',
        'currency',
        'status',
        'purchased_at',
    ];

    protected $casts = [
        'price_paid'   => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public static function userOwns(int $userId, int $questionSetId): bool
    {
        return self::where('user_id', $userId)
            ->where('question_set_id', $questionSetId)
            ->where('status', 'completed')
            ->exists();
    }
}
