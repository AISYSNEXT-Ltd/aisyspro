<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class LeadController extends CrudController
{
    protected string $modelClass = Lead::class;

    protected array $searchColumns = ['name', 'email', 'phone', 'company', 'source'];

    protected array $relations = ['assignee:id,name'];

    protected function rules(?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['new', 'contacted', 'qualified', 'lost', 'converted'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
