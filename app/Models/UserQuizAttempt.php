<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_set_id',
        'score',
        'total_questions',
        'percentage',
        'answers',
        'completed_at',
        'time_taken_seconds',
    ];

    protected $casts = [
        'answers' => 'array',
        'completed_at' => 'datetime',
        'percentage' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questionSet()
    {
        return $this->belongsTo(QuestionSet::class);
    }
}
