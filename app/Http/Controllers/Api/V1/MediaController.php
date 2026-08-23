<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::query()->latest();
        if ($search = trim((string) $request->query('search'))) {
            $query->where(fn ($q) => $q->where('original_name', 'like', "%{$search}%")->orWhere('alt_text', 'like', "%{$search}%"));
        }

        return response()->json($query->paginate(in_array($request->integer('per_page'), [12, 24, 48], true) ? $request->integer('per_page') : 24));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,pdf', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);
        $file = $data['file'];
        $path = $file->store('cms/'.now()->format('Y/m'), 'public');
        $media = Media::query()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
            'alt_text' => $data['alt_text'] ?? null,
            'uploaded_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $media], 201);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $media->update($request->validate(['alt_text' => ['nullable', 'string', 'max:255']]));

        return response()->json(['data' => $media->refresh()]);
    }

    public function destroy(Media $media): JsonResponse
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return response()->json(status: 204);
    }
}
