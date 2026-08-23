<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteSettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => SiteSetting::query()->orderBy('group')->orderBy('key')->get()->groupBy('group')]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.group' => ['required', 'string', 'max:40'],
            'settings.*.key' => ['required', 'alpha_dash', 'max:120'],
            'settings.*.value' => ['nullable'],
        ]);
        DB::transaction(function () use ($data): void {
            foreach ($data['settings'] as $setting) {
                SiteSetting::query()->updateOrCreate(['key' => $setting['key']], $setting);
            }
        });

        return $this->index();
    }
}
