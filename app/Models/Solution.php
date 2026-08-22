<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'short_description', 'description', 'status', 'featured', 'sort_order'])]
class Solution extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['featured' => 'boolean', 'sort_order' => 'integer'];
    }
}
