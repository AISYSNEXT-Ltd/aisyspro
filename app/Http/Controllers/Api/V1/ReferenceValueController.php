<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReferenceValue;
use App\Support\ReferenceGroups;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReferenceValueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ReferenceValue::query()->orderBy('group_key')->orderBy('sort_order')->orderBy('id');
        if ($request->filled('group_key')) {
            $query->where('group_key', $request->string('group_key'));
        }
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        return response()->json([
            'data' => $query->get(),
            'groups' => collect(ReferenceGroups::LABELS)->map(
                fn (string $label, string $key) => ['key' => $key, 'label' => $label],
            )->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $value = $this->persist(new ReferenceValue, $request);

        return response()->json(['data' => $value], 201);
    }

    public function update(Request $request, ReferenceValue $referenceValue): JsonResponse
    {
        return response()->json(['data' => $this->persist($referenceValue, $request)]);
    }

    public function destroy(ReferenceValue $referenceValue): JsonResponse
    {
        if ($this->usageCount($referenceValue) > 0) {
            return response()->json(['message' => 'Cette valeur est déjà utilisée. Désactivez-la au lieu de la supprimer.'], 422);
        }
        $referenceValue->delete();

        return response()->json(status: 204);
    }

    private function persist(ReferenceValue $value, Request $request): ReferenceValue
    {
        $data = $request->validate([
            'group_key' => ['required', Rule::in(array_keys(ReferenceGroups::LABELS))],
            'code' => ['required', 'alpha_dash:ascii', 'max:100', Rule::unique('reference_values')->where('group_key', $request->string('group_key'))->ignore($value->id)],
            'label' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_default' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($data['is_default'] && ! $data['is_active']) {
            throw ValidationException::withMessages(['is_active' => 'La valeur par défaut doit rester active.']);
        }
        if ($value->exists && $value->is_default && ! $data['is_default']) {
            $hasReplacement = ReferenceValue::query()->where('group_key', $value->group_key)
                ->whereKeyNot($value->id)->where('is_default', true)->where('is_active', true)->exists();
            if (! $hasReplacement) {
                throw ValidationException::withMessages(['is_default' => 'Définissez d’abord une autre valeur par défaut pour ce groupe.']);
            }
        }

        if ($value->exists && ($value->group_key !== $data['group_key'] || $value->code !== $data['code']) && $this->usageCount($value) > 0) {
            abort(422, 'Le code et le groupe d’une valeur utilisée ne peuvent pas être modifiés.');
        }

        return DB::transaction(function () use ($value, $data): ReferenceValue {
            if ($data['is_default']) {
                ReferenceValue::query()->where('group_key', $data['group_key'])->update(['is_default' => false]);
            }
            $value->fill($data)->save();

            return $value->refresh();
        });
    }

    private function usageCount(ReferenceValue $value): int
    {
        $mapping = [
            'lead_status' => ['leads', 'status'], 'lead_source' => ['leads', 'source'],
            'quote_status' => ['quotes', 'status'], 'client_status' => ['clients', 'status'],
            'task_status' => ['tasks', 'status'], 'task_priority' => ['tasks', 'priority'],
            'inquiry_status' => ['inquiries', 'status'],
        ];
        if (! isset($mapping[$value->group_key])) {
            return 0;
        }
        [$table, $column] = $mapping[$value->group_key];

        return DB::table($table)->where($column, $value->code)->count();
    }
}
