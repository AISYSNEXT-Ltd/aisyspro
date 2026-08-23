<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

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
