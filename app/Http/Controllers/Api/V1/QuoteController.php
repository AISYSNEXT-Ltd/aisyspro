<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class QuoteController extends CrudController
{
    protected string $modelClass = Quote::class;

    protected array $searchColumns = ['reference', 'notes'];

    protected array $relations = ['client:id,name,company'];

    protected function rules(?Model $model = null): array
    {
        return [
            'reference' => ['required', 'string', 'max:80', Rule::unique('quotes')->ignore($model?->getKey())],
            'client_id' => ['required', 'exists:clients,id'],
            'status' => ['required', Rule::in(['draft', 'sent', 'accepted', 'rejected'])],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
