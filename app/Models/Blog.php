<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'cover_url',
        'category',
        'author',
        'read_time',
        'likes',
        'views',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'likes' => 'integer',
        'views' => 'integer',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];
}
