<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicInquiryRequest;
use App\Models\Inquiry;
use App\Models\Lead;
use App\Models\OfferOption;
use App\Models\Pack;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PublicInquiryController extends Controller
{
    private const CONSENT_VERSION = 'privacy-v1-2026-09-06';

    public function store(StorePublicInquiryRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Cache::lock("public-inquiry:{$data['submission_uuid']}", 10)->block(3, function () use ($data, $request): JsonResponse {
            $existing = Inquiry::query()->where('submission_uuid', $data['submission_uuid'])->first();

            if ($existing) {
                return $this->response($existing);
            }

            $inquiry = DB::transaction(function () use ($data, $request): Inquiry {
                $pack = $data['type'] === 'quote'
                    ? Pack::query()->where('status', 'published')->where('slug', $data['pack_slug'])->firstOrFail()
                    : null;
                $options = $data['type'] === 'quote'
                    ? OfferOption::query()->where('status', 'published')
                        ->whereIn('slug', $data['option_slugs'] ?? [])->orderBy('sort_order')->get()
                    : collect();
                $estimatedTotal = $pack
                    ? (float) $pack->price + $options->sum(fn (OfferOption $option): float => (float) $option->price)
                    : null;

                $inquiry = Inquiry::query()->create([
                    'pack_id' => $pack?->id,
                    'type' => $data['type'],
                    'name' => trim($data['name']),
                    'email' => mb_strtolower(trim($data['email'])),
                    'phone' => $data['phone'] ?? null,
                    'company' => $data['company'] ?? null,
                    'subject' => $data['subject'] ?? ($pack ? "Demande de devis — {$pack->name}" : null),
                    'requested_solution' => $pack?->name ?? ($data['requested_solution'] ?? null),
                    'activity' => $data['activity'] ?? null,
                    'company_size' => $data['company_size'] ?? null,
                    'user_count' => $data['user_count'] ?? null,
                    'current_tools' => $data['current_tools'] ?? [],
                    'selected_options' => $options->map(fn (OfferOption $option): array => [
                        'slug' => $option->slug,
                        'name' => $option->name,
                        'price' => (float) $option->price,
                    ])->values()->all(),
                    'hosting_preference' => $data['hosting_preference'] ?? null,
                    'desired_timeline' => $data['desired_timeline'] ?? null,
                    'budget_range' => $data['budget_range'] ?? null,
                    'estimated_total' => $estimatedTotal,
                    'message' => $data['message'] ?? 'Demande transmise depuis le configurateur AISYSPRO.',
                    'status' => 'new',
                    'consent_accepted_at' => now(),
                    'consent_version' => self::CONSENT_VERSION,
                    'consent_ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
                    'submission_uuid' => $data['submission_uuid'],
                    'source_url' => $data['source_url'] ?? null,
                ]);

                $lead = Lead::query()->create([
                    'name' => $inquiry->name,
                    'email' => $inquiry->email,
                    'phone' => $inquiry->phone,
                    'company' => $inquiry->company,
                    'source' => $inquiry->type === 'quote' ? 'portal_quote' : 'portal_contact',
                    'status' => 'new',
                    'value' => $estimatedTotal ?? 0,
                    'notes' => $this->leadNotes($inquiry),
                ]);

                $inquiry->update(['lead_id' => $lead->id]);

                return $inquiry->refresh();
            });

            return $this->response($inquiry, 201);
        });
    }

    private function response(Inquiry $inquiry, int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => 'Votre demande a bien été enregistrée.',
            'data' => [
                'reference' => sprintf('AISYSPRO-%06d', $inquiry->id),
                'estimated_total' => $inquiry->estimated_total === null ? null : (float) $inquiry->estimated_total,
            ],
        ], $status);
    }

    private function leadNotes(Inquiry $inquiry): string
    {
        return collect([
            sprintf('Demande AISYSPRO-%06d transmise depuis le portail.', $inquiry->id),
            $inquiry->activity ? "Activité : {$inquiry->activity}" : null,
            $inquiry->requested_solution ? "Offre : {$inquiry->requested_solution}" : null,
            $inquiry->desired_timeline ? "Délai : {$inquiry->desired_timeline}" : null,
            $inquiry->budget_range ? "Budget : {$inquiry->budget_range}" : null,
        ])->filter()->implode("\n");
    }
}
