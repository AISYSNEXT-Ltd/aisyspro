<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\SitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function (): void {
    Route::middleware('throttle:login')->post('/auth/login', [AuthController::class, 'login']);

    if (app()->environment('staging')) {
        Route::get('/auth/session-status', function (Request $request) {
            $cookieName = (string) config('session.cookie');
            $sessionKeys = array_keys($request->session()->all());

            return response()->json([
                'cookie_name' => $cookieName,
                'cookie_received' => $request->cookies->has($cookieName),
                'authenticated' => Auth::check(),
                'session_has_auth_key' => collect($sessionKeys)
                    ->contains(fn (string $key): bool => str_starts_with($key, 'login_web_')),
                'session_driver' => config('session.driver'),
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        });
    }

    Route::middleware('auth')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::put('/profile/password', [ProfileController::class, 'password']);
    });
});

if (app()->environment('staging')) {
    Route::get('/session-diagnostic', function (Request $request) {
        $cookieName = (string) config('session.cookie');
        $sessionKeys = array_keys($request->session()->all());

        return response()->json([
            'cookie_name' => $cookieName,
            'cookie_received' => $request->cookies->has($cookieName),
            'authenticated' => Auth::check(),
            'session_has_auth_key' => collect($sessionKeys)
                ->contains(fn (string $key): bool => str_starts_with($key, 'login_web_')),
            'session_driver' => config('session.driver'),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    });
}

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', fn () => response(
    "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /connexion-admin\n\nSitemap: ".url('/sitemap.xml')."\n",
    200,
    ['Content-Type' => 'text/plain; charset=UTF-8'],
))->name('robots');

Route::view('/{path?}', 'app')
    ->where('path', '^(?:|solutions(?:/[^/]+)?|packs|blog(?:/[^/]+)?|a-propos|faq|contact|devis|mentions-legales|confidentialite|connexion-admin|admin(?:/.*)?)$')
    ->name('spa');

Route::get('/{path}', fn () => response()->view('app', status: 404))
    ->where('path', '^(?!api(?:/|$)|up$|sitemap\.xml$|robots\.txt$).*$')
    ->name('not-found');
