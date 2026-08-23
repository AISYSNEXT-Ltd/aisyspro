<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title', 'slug', 'category', 'excerpt', 'content', 'tags', 'hero_image', 'featured',
    'sort_order', 'meta_title', 'meta_description', 'canonical_url', 'og_title',
    'og_description', 'og_image', 'robots', 'status', 'published_at', 'author_id',
])]
class BlogPost extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'tags' => 'array',
            'featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
