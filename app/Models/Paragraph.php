<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paragraph extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'image',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class)
            ->orderByRaw('position IS NULL, position ASC, id ASC');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? url('storage/' . $this->image) : null;
    }
}
