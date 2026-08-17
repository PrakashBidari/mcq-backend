<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrialUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trialable_type',
        'trialable_id',
        'attempts_used',
        'first_used_at',
    ];

    protected $casts = [
        'attempts_used' => 'integer',
        'first_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trialable()
    {
        return $this->morphTo();
    }
}
