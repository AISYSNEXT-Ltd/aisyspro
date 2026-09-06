<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lead_id', 'pack_id', 'type', 'name', 'email', 'phone', 'company', 'subject',
    'requested_solution', 'activity', 'company_size', 'user_count', 'current_tools',
    'selected_options', 'hosting_preference', 'desired_timeline', 'budget_range',
    'estimated_total', 'message', 'status', 'assigned_to', 'processed_at',
    'consent_accepted_at', 'consent_version', 'consent_ip_hash', 'submission_uuid',
    'source_url',
])]
class Inquiry extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'current_tools' => 'array',
            'selected_options' => 'array',
            'estimated_total' => 'decimal:3',
            'processed_at' => 'datetime',
            'consent_accepted_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }
}
