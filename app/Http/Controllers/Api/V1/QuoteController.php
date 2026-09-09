<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Quote;
use App\Support\ReferenceGroups;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class QuoteController extends CrudController
{
    protected string $modelClass = Quote::class;

    protected array $searchColumns = ['reference', 'notes'];

    protected array $relations = ['client:id,name,company', 'lead:id,name,company'];

    protected function rules(?Model $model = null): array
    {
        return [
            'reference' => ['required', 'string', 'max:80', Rule::unique('quotes')->ignore($model?->getKey())],
            'client_id' => ['nullable', 'required_without:lead_id', 'exists:clients,id'],
            'lead_id' => ['nullable', 'required_without:client_id', 'exists:leads,id'],
            'status' => ['required', ReferenceGroups::activeRule('quote_status')],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
