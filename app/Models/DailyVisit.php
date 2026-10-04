<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyVisit extends Model
{
    protected $fillable = [
        'visit_date',
        'visitor_key',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
