<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title', 'slug', 'category', 'short_description', 'description', 'problem', 'value_proposition',
    'modules', 'benefits', 'tags', 'hero_image', 'meta_title', 'meta_description', 'status',
    'featured', 'sort_order',
])]
class Solution extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'modules' => 'array',
            'benefits' => 'array',
            'tags' => 'array',
            'featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
