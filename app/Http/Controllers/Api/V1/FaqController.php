<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class FaqController extends CrudController
{
    protected string $modelClass = Faq::class;

    protected array $searchColumns = ['question', 'answer', 'category'];

    protected function rules(?Model $model = null): array
    {
        return [
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
