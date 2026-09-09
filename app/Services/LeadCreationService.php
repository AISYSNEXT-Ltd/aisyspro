<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Quote;
use App\Support\ReferenceGroups;
use Illuminate\Support\Facades\DB;

class LeadCreationService
{
    public function createWithDraftQuote(array $data, ?int $userId = null): Lead
    {
        return DB::transaction(function () use ($data, $userId): Lead {
            $lead = Lead::query()->create($data);
            $quote = Quote::query()->create([
                'lead_id' => $lead->id,
                'is_initial' => true,
                'reference' => sprintf('DEV-%s-%06d', now()->format('Y'), $lead->id),
                'status' => ReferenceGroups::activeCode('quote_status', 'draft'),
                'subtotal' => $lead->value ?? 0,
                'tax' => 0,
                'total' => $lead->value ?? 0,
                'notes' => 'Devis brouillon créé automatiquement à l’enregistrement du prospect.',
            ]);
            $lead->activities()->create([
                'user_id' => $userId,
                'type' => 'quote_created',
                'description' => "Devis brouillon {$quote->reference} créé automatiquement.",
                'metadata' => ['quote_id' => $quote->id],
            ]);

            return $lead->load(['assignee:id,name', 'quotes', 'activities']);
        });
    }
}
