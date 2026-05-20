<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'explanation',
        'difficulty',
        'correct_answer',
        'position',
    ];

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('option_index');
    }

    public function questionSets()
    {
        return $this->belongsToMany(QuestionSet::class, 'question_question_set')
                    ->withPivot('order')
                    ->withTimestamps();
    }
}
