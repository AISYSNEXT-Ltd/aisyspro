<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CrudService
{
    public function paginate(
        string $modelClass,
        Request $request,
        array $searchColumns,
        array $relations = [],
    ): LengthAwarePaginator {
        /** @var Builder $query */
        $query = $modelClass::query()->with($relations);
        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function (Builder $nested) use ($search, $searchColumns): void {
                foreach ($searchColumns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $nested->{$method}($column, 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $perPage = in_array($request->integer('per_page'), [5, 10, 25, 50, 100], true)
            ? $request->integer('per_page')
            : 10;

        return $query->latest('id')->paginate($perPage)->withQueryString();
    }

    public function create(string $modelClass, array $data): Model
    {
        return $modelClass::query()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->refresh();
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
