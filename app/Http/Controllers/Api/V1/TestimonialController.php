<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class TestimonialController extends CrudController
{
    protected string $modelClass = Testimonial::class;

    protected array $searchColumns = ['name', 'company', 'role', 'content'];

    protected function rules(?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:3000'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'status' => ['required', Rule::in(['draft', 'published', 'hidden'])],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
