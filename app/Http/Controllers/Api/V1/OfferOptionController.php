<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\OfferOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class OfferOptionController extends CrudController
{
    protected string $modelClass = OfferOption::class;

    protected array $searchColumns = ['name', 'slug', 'description'];

    protected function rules(?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('offer_options')->ignore($model?->getKey())],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
