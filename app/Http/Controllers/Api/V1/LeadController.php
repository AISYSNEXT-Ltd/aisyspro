<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Lead;
use App\Models\ReferenceValue;
use App\Services\CrudService;
use App\Services\LeadCreationService;
use App\Support\ReferenceGroups;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeadController extends CrudController
{
    protected string $modelClass = Lead::class;

    protected array $searchColumns = ['name', 'email', 'phone', 'company', 'source'];

    protected array $relations = ['assignee:id,name', 'quotes:id,lead_id,reference,status,total,is_initial'];

    public function __construct(CrudService $service, private LeadCreationService $leadCreation)
    {
        parent::__construct($service);
    }

    protected function rules(?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:255'],
            'submission_uuid' => ['nullable', 'uuid', 'unique:leads,submission_uuid'.($model ? ",{$model->id}" : '')],
            'source' => ['nullable', ReferenceGroups::activeRule('lead_source')],
            'status' => ['required', ReferenceGroups::activeRule('lead_status')],
            'value' => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->filled('submission_uuid')) {
            $uuid = $request->string('submission_uuid')->toString();

            return Cache::lock("lead-create:{$uuid}", 10)->block(3, fn (): JsonResponse => $this->createOrReturnExisting($request, $uuid));
        }

        $data = $request->validate($this->rules());
        $lead = $this->leadCreation->createWithDraftQuote($data, $request->user()?->id);

        return response()->json(['data' => $lead], 201);
    }

    private function createOrReturnExisting(Request $request, string $uuid): JsonResponse
    {
        $existing = Lead::query()->where('submission_uuid', $uuid)->first();
        if ($existing) {
            return response()->json(['data' => $existing->load($this->relations)]);
        }

        $data = $request->validate($this->rules());
        $lead = $this->leadCreation->createWithDraftQuote($data, $request->user()?->id);

        return response()->json(['data' => $lead], 201);
    }

    public function pipeline(Request $request): JsonResponse
    {
        $stages = ReferenceValue::query()->where('group_key', 'lead_status')->where('is_active', true)
            ->orderBy('sort_order')->get();
        $leads = Lead::query()->with($this->relations)->when($request->filled('search'), function ($query) use ($request): void {
            $search = $request->string('search');
            $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('company', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        })->orderByDesc('id')->get();

        return response()->json(['data' => $stages->map(fn ($stage) => [
            'stage' => $stage,
            'leads' => $leads->where('status', $stage->code)->values(),
        ])]);
    }

    public function move(Request $request, Lead $lead): JsonResponse
    {
        $data = $request->validate(['status' => ['required', ReferenceGroups::activeRule('lead_status')]]);
        $previous = $lead->status;
        DB::transaction(function () use ($lead, $data, $previous, $request): void {
            $lead->update(['status' => $data['status']]);
            $lead->activities()->create([
                'user_id' => $request->user()?->id,
                'type' => 'status_changed',
                'description' => "Étape modifiée de {$previous} vers {$data['status']}.",
                'metadata' => ['from' => $previous, 'to' => $data['status']],
            ]);
        });

        return response()->json(['data' => $lead->refresh()->load($this->relations)]);
    }
}
