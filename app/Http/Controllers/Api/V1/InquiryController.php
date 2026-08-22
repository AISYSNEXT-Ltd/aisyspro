<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class InquiryController extends CrudController
{
    protected string $modelClass = Inquiry::class;

    protected array $searchColumns = ['name', 'email', 'phone', 'company', 'subject', 'requested_solution', 'message'];

    protected array $relations = ['assignee:id,name'];

    protected function rules(?Model $model = null): array
    {
        return [
            'type' => ['required', Rule::in(['contact', 'quote'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'requested_solution' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['new', 'in_progress', 'closed'])],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'processed_at' => ['nullable', 'date'],
        ];
    }
}
