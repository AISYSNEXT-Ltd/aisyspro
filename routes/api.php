<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlogPostController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\QuoteController;
use App\Http\Controllers\Api\V1\SolutionController;
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->name('api.v1.health');
    Route::middleware(['web', 'throttle:login'])->post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::middleware('web')->group(function (): void {
            Route::get('/auth/me', [AuthController::class, 'me']);
            Route::post('/auth/logout', [AuthController::class, 'logout']);
        });
        Route::get('/dashboard', DashboardController::class);
        Route::middleware('role:administrateur,commercial')->group(function (): void {
            Route::apiResource('clients', ClientController::class)->parameters(['clients' => 'id']);
            Route::apiResource('leads', LeadController::class)->parameters(['leads' => 'id']);
            Route::apiResource('quotes', QuoteController::class)->parameters(['quotes' => 'id']);
            Route::apiResource('tasks', TaskController::class)->parameters(['tasks' => 'id']);
        });
        Route::middleware('role:administrateur,editeur')->group(function (): void {
            Route::apiResource('blog-posts', BlogPostController::class)->parameters(['blog-posts' => 'id']);
            Route::apiResource('solutions', SolutionController::class)->parameters(['solutions' => 'id']);
        });
    });
});
