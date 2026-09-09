<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group_key', 'code', 'label', 'color', 'sort_order', 'is_default', 'is_active'])]
class ReferenceValue extends Model
{
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_default' => 'boolean', 'is_active' => 'boolean'];
    }
}
