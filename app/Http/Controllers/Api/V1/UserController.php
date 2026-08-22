<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends CrudController
{
    protected string $modelClass = User::class;

    protected array $searchColumns = ['name', 'login', 'email'];

    protected array $relations = ['role:id,name,slug'];

    protected function rules(?Model $model = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:80', Rule::unique('users', 'login')->ignore($model?->getKey())],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($model?->getKey())],
            'password' => [$model ? 'nullable' : 'required', 'string', 'min:10', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        return parent::store($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (! $request->filled('password')) {
            $request->request->remove('password');
            $request->request->remove('password_confirmation');
        }

        return parent::update($request, $id);
    }

    public function destroy(int $id): JsonResponse
    {
        abort_if(request()->user()->id === $id, 422, 'Vous ne pouvez pas supprimer votre propre compte.');

        return parent::destroy($id);
    }
}
