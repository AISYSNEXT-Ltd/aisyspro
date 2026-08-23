<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Menu::query()->withCount('items')->orderBy('name')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $menu = Menu::query()->create($request->validate($this->rules()));

        return response()->json(['data' => $menu], 201);
    }

    public function update(Request $request, Menu $menu): JsonResponse
    {
        $menu->update($request->validate($this->rules($menu)));

        return response()->json(['data' => $menu->refresh()]);
    }

    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return response()->json(status: 204);
    }

    private function rules(?Menu $menu = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'alpha_dash', 'max:40', Rule::unique('menus')->ignore($menu?->id)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
