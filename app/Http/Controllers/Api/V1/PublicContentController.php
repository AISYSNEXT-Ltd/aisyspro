<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Pack;
use App\Models\Solution;
use Illuminate\Http\JsonResponse;

class PublicContentController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'solutions' => Solution::query()->where('status', 'published')->orderBy('sort_order')->limit(12)->get(),
            'packs' => Pack::query()->where('status', 'published')->orderByDesc('featured')->orderBy('sort_order')->get(),
            'faqs' => Faq::query()->where('status', 'published')->orderBy('sort_order')->get(),
            'posts' => BlogPost::query()->where('status', 'published')->whereNotNull('published_at')
                ->where('published_at', '<=', now())->latest('published_at')->limit(3)->get(),
        ]);
    }
}
