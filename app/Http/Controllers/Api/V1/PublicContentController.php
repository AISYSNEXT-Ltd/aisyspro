<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Pack;
use App\Models\Solution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicContentController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'solutions' => Solution::query()->where('status', 'published')->orderByDesc('featured')->orderBy('sort_order')->limit(5)->get(),
            'packs' => Pack::query()->where('status', 'published')->orderByDesc('featured')->orderBy('sort_order')->get(),
            'faqs' => Faq::query()->where('status', 'published')->orderBy('sort_order')->get(),
            'posts' => BlogPost::query()->where('status', 'published')->whereNotNull('published_at')
                ->where('published_at', '<=', now())->orderByDesc('featured')->latest('published_at')->limit(3)->get(),
        ]);
    }

    public function solutions(Request $request): JsonResponse
    {
        $query = Solution::query()->where('status', 'published');
        $this->filter($query, $request, ['title', 'short_description', 'description', 'category']);

        return response()->json([
            'data' => $query->orderBy('sort_order')->get(),
            'categories' => Solution::query()->where('status', 'published')->whereNotNull('category')
                ->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function solution(string $slug): JsonResponse
    {
        return response()->json([
            'data' => Solution::query()->where('status', 'published')->where('slug', $slug)->firstOrFail(),
        ]);
    }

    public function posts(Request $request): JsonResponse
    {
        $query = BlogPost::query()->where('status', 'published')->whereNotNull('published_at')
            ->where('published_at', '<=', now());
        $this->filter($query, $request, ['title', 'excerpt', 'content', 'category']);

        return response()->json([
            'data' => $query->orderByDesc('featured')->orderBy('sort_order')->latest('published_at')->get(),
            'categories' => BlogPost::query()->where('status', 'published')->whereNotNull('category')
                ->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function post(string $slug): JsonResponse
    {
        return response()->json([
            'data' => BlogPost::query()->where('status', 'published')->where('slug', $slug)
                ->whereNotNull('published_at')->where('published_at', '<=', now())->firstOrFail(),
        ]);
    }

    private function filter($query, Request $request, array $columns): void
    {
        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($inner) use ($columns, $search): void {
                foreach ($columns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $inner->{$method}($column, 'like', "%{$search}%");
                }
            });
        }

        if ($category = trim((string) $request->query('category'))) {
            $query->where('category', $category);
        }
    }
}
