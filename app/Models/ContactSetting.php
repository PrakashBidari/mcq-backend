<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'email',
        'phone',
        'address',
        'form_title',
        'form_subtitle'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
