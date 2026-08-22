<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description', 'price', 'billing_period', 'features', 'status', 'featured', 'sort_order'])]
class Pack extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['price' => 'decimal:3', 'features' => 'array', 'featured' => 'boolean', 'sort_order' => 'integer'];
    }
}
