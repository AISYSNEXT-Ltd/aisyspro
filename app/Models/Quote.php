<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reference', 'client_id', 'status', 'subtotal', 'tax', 'total', 'valid_until', 'notes'])]
class Quote extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:3', 'tax' => 'decimal:3', 'total' => 'decimal:3', 'valid_until' => 'date'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
