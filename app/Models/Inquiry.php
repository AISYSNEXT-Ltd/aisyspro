<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['type', 'name', 'email', 'phone', 'company', 'subject', 'requested_solution', 'message', 'status', 'assigned_to', 'processed_at'])]
class Inquiry extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
