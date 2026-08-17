<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttemptWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'delta',
        'reason',
        'reference_type',
        'reference_id',
        'balance_after',
    ];

    protected $casts = [
        'delta' => 'integer',
        'balance_after' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function latestBalance(int $userId): int
    {
        return static::where('user_id', $userId)
            ->orderByDesc('id')
            ->value('balance_after') ?? 0;
    }
}
