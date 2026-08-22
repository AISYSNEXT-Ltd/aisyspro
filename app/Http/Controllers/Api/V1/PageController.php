<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PageController extends CrudController
{
    protected string $modelClass = Page::class;

    protected array $searchColumns = ['title', 'slug', 'excerpt', 'content'];

    protected function rules(?Model $model = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('pages')->ignore($model?->getKey())],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
