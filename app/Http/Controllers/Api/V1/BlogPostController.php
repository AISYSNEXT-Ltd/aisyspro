<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogPostController extends CrudController
{
    protected string $modelClass = BlogPost::class;

    protected array $searchColumns = ['title', 'excerpt', 'content'];

    protected array $relations = ['author:id,name'];

    protected function rules(?Model $model = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('blog_posts')->ignore($model?->getKey())],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'author_id' => ['nullable', 'exists:users,id'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge(['author_id' => $request->user()->id]);

        return parent::store($request);
    }
}
