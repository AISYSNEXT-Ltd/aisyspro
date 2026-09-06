<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description', 'price', 'status', 'sort_order'])]
class OfferOption extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['price' => 'decimal:3', 'sort_order' => 'integer'];
    }
}
