<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'tagline',
        'last_updated_label',
        'intro_text_1',
        'intro_text_2',
        'button_text',
        'stat_1_value',
        'stat_2_value',
        'stat_3_value',
        'stat_4_value',
        'developer_name',
        'developer_role',
        'developer_url',
        'copyright_text',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];
}
