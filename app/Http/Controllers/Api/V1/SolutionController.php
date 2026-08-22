<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Solution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class SolutionController extends CrudController
{
    protected string $modelClass = Solution::class;

    protected array $searchColumns = ['title', 'short_description', 'description'];

    protected function rules(?Model $model = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('solutions')->ignore($model?->getKey())],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
