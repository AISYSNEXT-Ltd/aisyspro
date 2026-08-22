<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => Role::query()->orderBy('name')->get(['id', 'name', 'slug'])]);
    }
}
