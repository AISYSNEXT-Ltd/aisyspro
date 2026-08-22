<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CrudService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class CrudController extends Controller
{
    protected string $modelClass;

    protected array $searchColumns = ['name'];

    protected array $relations = [];

    public function __construct(protected CrudService $service) {}

    abstract protected function rules(?Model $model = null): array;

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->service->paginate(
            $this->modelClass,
            $request,
            $this->searchColumns,
            $this->relations,
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $model = $this->service->create($this->modelClass, $request->validate($this->rules()));

        return response()->json(['data' => $model->load($this->relations)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $model = $this->find($id);

        return response()->json(['data' => $model->load($this->relations)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $model = $this->find($id);
        $model = $this->service->update($model, $request->validate($this->rules($model)));

        return response()->json(['data' => $model->load($this->relations)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $model = $this->find($id);
        $this->service->delete($model);

        return response()->json(status: 204);
    }

    protected function find(int $id): Model
    {
        return $this->modelClass::query()->findOrFail($id);
    }
}
