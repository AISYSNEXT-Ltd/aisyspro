<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'phone', 'company', 'city', 'status', 'notes'])]
class Client extends Model
{
    use HasFactory;

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }
}
