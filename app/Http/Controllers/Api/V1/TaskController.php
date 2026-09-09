<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Task;
use App\Support\ReferenceGroups;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends CrudController
{
    protected string $modelClass = Task::class;

    protected array $searchColumns = ['title', 'description'];

    protected array $relations = ['assignee:id,name'];

    protected function rules(?Model $model = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', ReferenceGroups::activeRule('task_status')],
            'priority' => ['required', ReferenceGroups::activeRule('task_priority')],
            'due_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'created_by' => ['nullable', 'exists:users,id'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge(['created_by' => $request->user()->id]);

        return parent::store($request);
    }
}
