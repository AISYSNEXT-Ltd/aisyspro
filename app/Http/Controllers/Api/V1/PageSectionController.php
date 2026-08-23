<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PageSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PageSection::query()->with('page:id,title,slug')->orderBy('sort_order');

        if ($request->filled('page_id')) {
            $query->where('page_id', $request->integer('page_id'));
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $section = PageSection::query()->create($request->validate($this->rules()));

        return response()->json(['data' => $section->load('page:id,title,slug')], 201);
    }

    public function update(Request $request, PageSection $pageSection): JsonResponse
    {
        $pageSection->update($request->validate($this->rules()));

        return response()->json(['data' => $pageSection->refresh()->load('page:id,title,slug')]);
    }

    public function destroy(PageSection $pageSection): JsonResponse
    {
        $pageSection->delete();

        return response()->json(status: 204);
    }

    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:page_sections,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($data): void {
            foreach ($data['items'] as $item) {
                PageSection::query()->whereKey($item['id'])->update(['sort_order' => $item['sort_order']]);
            }
        });

        return response()->json(['message' => 'Ordre des sections mis à jour.']);
    }

    private function rules(): array
    {
        return [
            'page_id' => ['required', 'exists:pages,id'],
            'type' => ['required', Rule::in(['hero', 'text', 'image-text', 'gallery', 'cards', 'benefits', 'testimonials', 'stats', 'faq', 'cta', 'logos', 'banner', 'columns', 'custom', 'form', 'blog', 'solutions'])],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'settings' => ['nullable', 'array'],
            'display_mode' => ['required', Rule::in(['append', 'replace'])],
            'animation' => ['required', Rule::in(['none', 'fade', 'slide-up', 'slide-left', 'slide-right', 'zoom', 'scale', 'reveal'])],
            'duration' => ['required', 'integer', 'between:0,3000'],
            'delay' => ['required', 'integer', 'between:0,3000'],
            'intensity' => ['required', 'integer', 'between:0,100'],
            'is_visible' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
