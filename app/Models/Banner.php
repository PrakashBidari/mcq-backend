<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'image_url',
        'link_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // Resolves the display URL: an uploaded file wins over a pasted external URL.
    public function getDisplayImageAttribute(): ?string
    {
        if ($this->image) {
            return url('storage/' . $this->image);
        }

        return $this->image_url;
    }
}
