<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Inquiry;
use App\Support\ReferenceGroups;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class InquiryController extends CrudController
{
    protected string $modelClass = Inquiry::class;

    protected array $searchColumns = ['name', 'email', 'phone', 'company', 'subject', 'requested_solution', 'message'];

    protected array $relations = ['assignee:id,name', 'lead:id,name,status', 'pack:id,name,slug'];

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
            'activity' => ['nullable', 'string', 'max:255'],
            'company_size' => ['nullable', Rule::in(['solo', '2-10', '11-50', '51-200', '200-plus'])],
            'user_count' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'hosting_preference' => ['nullable', Rule::in(['included', 'existing', 'undecided'])],
            'desired_timeline' => ['nullable', Rule::in(['urgent', '1-3-months', '3-6-months', 'flexible'])],
            'budget_range' => ['nullable', Rule::in(['under-1000', '1000-3000', '3000-10000', 'over-10000', 'undecided'])],
            'estimated_total' => ['nullable', 'numeric', 'min:0'],
            'message' => ['required', 'string', 'max:5000'],
            'status' => ['required', ReferenceGroups::activeRule('inquiry_status')],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'processed_at' => ['nullable', 'date'],
        ];
    }
}
