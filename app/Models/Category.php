<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
    ];

    public function questionSets()
    {
        return $this->hasMany(QuestionSet::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function isSubcategory(): bool
    {
        return !is_null($this->parent_id);
    }

    // Get all questions through question sets
    public function questions()
    {
        return $this->hasManyThrough(
            Question::class,
            QuestionSet::class,
            'category_id',      // Foreign key on question_sets
            'id',               // Foreign key on questions
            'id',               // Local key on categories
            'id'                // Local key on question_sets
        )->join('question_question_set', 'questions.id', '=', 'question_question_set.question_id')
         ->where('question_question_set.question_set_id', '=', \DB::raw('question_sets.id'));
    }
}
