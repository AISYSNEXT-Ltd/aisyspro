<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Pack;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PackController extends CrudController
{
    protected string $modelClass = Pack::class;

    protected array $searchColumns = ['name', 'slug', 'description'];

    protected function rules(?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('packs')->ignore($model?->getKey())],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'billing_period' => ['nullable', 'string', 'max:40'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'featured' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
