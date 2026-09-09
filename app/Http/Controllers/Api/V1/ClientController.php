<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Client;
use App\Support\ReferenceGroups;
use Illuminate\Database\Eloquent\Model;

class ClientController extends CrudController
{
    protected string $modelClass = Client::class;

    protected array $searchColumns = ['name', 'email', 'phone', 'company'];

    protected function rules(?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'status' => ['required', ReferenceGroups::activeRule('client_status')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
