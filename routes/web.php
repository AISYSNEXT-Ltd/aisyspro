<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\SitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function (): void {
    Route::middleware('throttle:login')->post('/auth/login', [AuthController::class, 'login']);

    if (app()->environment('staging')) {
        Route::get('/auth/session-status', function (Request $request) {
            $cookieName = (string) config('session.cookie');
            $sessionKeys = array_keys($request->session()->all());
            $sessionTable = (string) config('session.table', 'sessions');
            $currentSession = DB::table($sessionTable)
                ->where('id', $request->session()->getId())
                ->first(['user_id']);

            return response()->json([
                'cookie_name' => $cookieName,
                'cookie_received' => $request->cookies->has($cookieName),
                'authenticated' => Auth::check(),
                'session_has_auth_key' => collect($sessionKeys)
                    ->contains(fn (string $key): bool => str_starts_with($key, 'login_web_')),
                'session_driver' => config('session.driver'),
                'current_session_record_exists' => $currentSession !== null,
                'current_session_record_authenticated' => $currentSession?->user_id !== null,
                'recent_authenticated_sessions' => DB::table($sessionTable)
                    ->whereNotNull('user_id')
                    ->where('last_activity', '>=', now()->subMinutes(10)->timestamp)
                    ->count(),
            ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        });
    }

    Route::middleware('auth')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::put('/profile/password', [ProfileController::class, 'password']);
    });
});

// Browser session login deliberately lives outside /api. Some reverse-proxy
// configurations treat API response cookies differently; this web endpoint is
// covered by the dedicated /connexion-admin Varnish exclusion.
Route::middleware('throttle:login')
    ->post('/connexion-admin/session', [AuthController::class, 'login'])
    ->name('auth.session.login');

if (app()->environment('staging')) {
    Route::get('/controle-staging', function (Request $request) {
        $cookieName = (string) config('session.cookie');
        $sessionKeys = array_keys($request->session()->all());
        $sessionTable = (string) config('session.table', 'sessions');
        $currentSession = DB::table($sessionTable)
            ->where('id', $request->session()->getId())
            ->first(['user_id']);

        $status = [
            'cookie_name' => $cookieName,
            'cookie_received' => $request->cookies->has($cookieName),
            'authenticated' => Auth::check(),
            'session_has_auth_key' => collect($sessionKeys)
                ->contains(fn (string $key): bool => str_starts_with($key, 'login_web_')),
            'session_driver' => config('session.driver'),
            'current_session_record_exists' => $currentSession !== null,
            'current_session_record_authenticated' => $currentSession?->user_id !== null,
            'recent_authenticated_sessions' => DB::table($sessionTable)
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMinutes(10)->timestamp)
                ->count(),
        ];

        $html = '<!doctype html><html lang="fr"><meta charset="utf-8"><title>Session staging</title>'
            .'<body><pre>'.e(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)).'</pre></body></html>';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
        ]);
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
