<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::query()->with('menu:id,name,location')->orderBy('sort_order');
        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->integer('menu_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $item = MenuItem::query()->create($request->validate($this->rules()));

        return response()->json(['data' => $item->load('menu:id,name,location')], 201);
    }

    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        $menuItem->update($request->validate($this->rules($menuItem)));

        return response()->json(['data' => $menuItem->refresh()->load('menu:id,name,location')]);
    }

    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $menuItem->delete();

        return response()->json(status: 204);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);
        DB::transaction(function () use ($data): void {
            foreach ($data['items'] as $item) {
                MenuItem::query()->whereKey($item['id'])->update(['sort_order' => $item['sort_order']]);
            }
        });

        return response()->json(['message' => 'Ordre du menu mis à jour.']);
    }

    private function rules(?MenuItem $item = null): array
    {
        return [
            'menu_id' => ['required', 'exists:menus,id'],
            'parent_id' => ['nullable', 'exists:menu_items,id', Rule::notIn(array_filter([$item?->id]))],
            'label' => ['required', 'string', 'max:120'],
            'url' => ['required', 'string', 'max:2048'],
            'link_type' => ['required', Rule::in(['internal', 'external'])],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'is_visible' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
