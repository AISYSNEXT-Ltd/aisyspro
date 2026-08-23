<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'company', 'role', 'content', 'rating', 'status', 'sort_order'])]
class Testimonial extends Model
{
    protected function casts(): array
    {
        return ['rating' => 'integer', 'sort_order' => 'integer'];
    }
}
