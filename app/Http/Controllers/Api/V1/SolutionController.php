<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Solution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class SolutionController extends CrudController
{
    protected string $modelClass = Solution::class;

    protected array $searchColumns = ['title', 'category', 'short_description', 'description'];

    protected function rules(?Model $model = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('solutions')->ignore($model?->getKey())],
            'category' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'problem' => ['nullable', 'string'],
            'value_proposition' => ['nullable', 'string'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', 'max:255'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:80'],
            'hero_image' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
