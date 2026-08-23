<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlogPostController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\InquiryController;
use App\Http\Controllers\Api\V1\LeadController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\MenuItemController;
use App\Http\Controllers\Api\V1\PackController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PageSectionController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PublicContentController;
use App\Http\Controllers\Api\V1\PublicInquiryController;
use App\Http\Controllers\Api\V1\QuoteController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SolutionController;
use App\Http\Controllers\Api\V1\SiteSettingController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthController::class)->name('api.v1.health');
    Route::middleware('cache.headers:public;max_age=300;etag')->group(function (): void {
        Route::get('/public/content', PublicContentController::class);
        Route::get('/public/solutions', [PublicContentController::class, 'solutions']);
        Route::get('/public/solutions/{slug}', [PublicContentController::class, 'solution']);
        Route::get('/public/posts', [PublicContentController::class, 'posts']);
        Route::get('/public/posts/{slug}', [PublicContentController::class, 'post']);
        Route::get('/public/pages/{slug}', [PublicContentController::class, 'page']);
        Route::get('/public/menus/{location}', [PublicContentController::class, 'menu']);
        Route::get('/public/settings', [PublicContentController::class, 'settings']);
    });
    Route::middleware('throttle:public-form')->post('/public/inquiries', [PublicInquiryController::class, 'store']);
    Route::middleware(['web', 'throttle:login'])->post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::middleware('web')->group(function (): void {
            Route::get('/auth/me', [AuthController::class, 'me']);
            Route::post('/auth/logout', [AuthController::class, 'logout']);
            Route::put('/profile/password', [ProfileController::class, 'password']);
        });
        Route::get('/dashboard', DashboardController::class);
        Route::middleware('role:administrateur,commercial')->group(function (): void {
            Route::apiResource('clients', ClientController::class)->parameters(['clients' => 'id']);
            Route::apiResource('leads', LeadController::class)->parameters(['leads' => 'id']);
            Route::apiResource('quotes', QuoteController::class)->parameters(['quotes' => 'id']);
            Route::apiResource('tasks', TaskController::class)->parameters(['tasks' => 'id']);
            Route::apiResource('inquiries', InquiryController::class)->parameters(['inquiries' => 'id']);
        });
        Route::middleware('role:administrateur,editeur')->group(function (): void {
            Route::apiResource('blog-posts', BlogPostController::class)->parameters(['blog-posts' => 'id']);
            Route::apiResource('solutions', SolutionController::class)->parameters(['solutions' => 'id']);
            Route::apiResource('packs', PackController::class)->parameters(['packs' => 'id']);
            Route::apiResource('faqs', FaqController::class)->parameters(['faqs' => 'id']);
            Route::apiResource('pages', PageController::class)->parameters(['pages' => 'id']);
            Route::apiResource('testimonials', TestimonialController::class)->parameters(['testimonials' => 'id']);
            Route::get('/page-sections', [PageSectionController::class, 'index']);
            Route::post('/page-sections', [PageSectionController::class, 'store']);
            Route::put('/page-sections/reorder', [PageSectionController::class, 'reorder']);
            Route::put('/page-sections/{pageSection}', [PageSectionController::class, 'update']);
            Route::delete('/page-sections/{pageSection}', [PageSectionController::class, 'destroy']);
            Route::apiResource('menus', MenuController::class)->except('show');
            Route::get('/menu-items', [MenuItemController::class, 'index']);
            Route::post('/menu-items', [MenuItemController::class, 'store']);
            Route::put('/menu-items/reorder', [MenuItemController::class, 'reorder']);
            Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update']);
            Route::delete('/menu-items/{menuItem}', [MenuItemController::class, 'destroy']);
            Route::apiResource('media', MediaController::class)->only(['index', 'store', 'update', 'destroy']);
        });
        Route::middleware('role:administrateur')->group(function (): void {
            Route::get('/roles', RoleController::class);
            Route::apiResource('users', UserController::class)->parameters(['users' => 'id']);
            Route::get('/settings', [SiteSettingController::class, 'index']);
            Route::put('/settings', [SiteSettingController::class, 'update']);
        });
    });
});
