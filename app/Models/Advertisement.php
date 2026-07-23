<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    // 'position' is intentionally excluded — it identifies the fixed app slot
    // this row renders in and must never change via a mass-assigned request.
    protected $fillable = [
        'title',
        'description',
        'button_text',
        'link_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
