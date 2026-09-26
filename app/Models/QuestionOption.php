<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text',
        'option_index',
        'option_image',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->option_image ? url('storage/' . $this->option_image) : null;
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
