<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'description',
        'cover',
        'image',
        'rating',
        'pages',
        'duration',
        'category_id',
        'difficulty',
        'students',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'pages' => 'integer',
        'students' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationship
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
