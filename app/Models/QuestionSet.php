<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'is_active',
        'is_paid',
        'price',
        'time_limit',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_paid'    => 'boolean',
        'price'      => 'decimal:2',
        'time_limit' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_question_set')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderBy('pivot_order');
    }
}
