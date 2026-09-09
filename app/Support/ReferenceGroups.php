<?php

namespace App\Support;

use App\Models\ReferenceValue;
use Illuminate\Validation\Rule;

final class ReferenceGroups
{
    public const LABELS = [
        'lead_status' => 'Statuts des prospects',
        'lead_source' => 'Sources des prospects',
        'quote_status' => 'Statuts des devis',
        'client_status' => 'Statuts des clients',
        'task_status' => 'Statuts des tâches',
        'task_priority' => 'Priorités des tâches',
        'inquiry_status' => 'Statuts des demandes',
    ];

    public static function activeRule(string $group)
    {
        return Rule::exists('reference_values', 'code')->where(
            fn ($query) => $query->where('group_key', $group)->where('is_active', true),
        );
    }

    public static function defaultCode(string $group, string $fallback): string
    {
        return ReferenceValue::query()->where('group_key', $group)->where('is_active', true)
            ->orderByDesc('is_default')->orderBy('sort_order')->value('code') ?? $fallback;
    }

    public static function activeCode(string $group, string $preferred): string
    {
        return ReferenceValue::query()->where('group_key', $group)->where('code', $preferred)
            ->where('is_active', true)->value('code') ?? self::defaultCode($group, $preferred);
    }
}
