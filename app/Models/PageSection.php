<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'page_id', 'type', 'title', 'content', 'settings', 'display_mode', 'animation',
    'duration', 'delay', 'intensity', 'is_visible', 'sort_order',
])]
class PageSection extends Model
{
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_visible' => 'boolean',
            'duration' => 'integer',
            'delay' => 'integer',
            'intensity' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
