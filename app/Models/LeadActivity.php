<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['lead_id', 'user_id', 'type', 'description', 'metadata'])]
class LeadActivity extends Model
{
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
