<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Solution;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => url('/'), 'lastmod' => now()->toDateString(), 'priority' => '1.0'],
            ...collect(['/solutions', '/packs', '/blog', '/a-propos', '/faq', '/contact', '/devis'])
                ->map(fn (string $path) => ['loc' => url($path), 'lastmod' => now()->toDateString(), 'priority' => '0.8'])->all(),
        ]);

        $urls = $urls->concat(Solution::query()->where('status', 'published')->get(['slug', 'updated_at'])
            ->map(fn (Solution $solution) => ['loc' => url('/solutions/'.$solution->slug), 'lastmod' => $solution->updated_at->toDateString(), 'priority' => '0.7']))
            ->concat(BlogPost::query()->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())
                ->get(['slug', 'updated_at'])->map(fn (BlogPost $post) => ['loc' => url('/blog/'.$post->slug), 'lastmod' => $post->updated_at->toDateString(), 'priority' => '0.6']))
            ->concat(Page::query()->where('status', 'published')->whereNotIn('slug', ['accueil', 'solutions', 'packs', 'blog', 'a-propos', 'faq', 'contact'])
                ->get(['slug', 'updated_at'])->map(fn (Page $page) => ['loc' => url('/'.$page->slug), 'lastmod' => $page->updated_at->toDateString(), 'priority' => '0.5']))
            ->unique('loc');

        $body = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $body .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $url) {
            $body .= '  <url><loc>'.e($url['loc']).'</loc><lastmod>'.$url['lastmod'].'</lastmod><priority>'.$url['priority'].'</priority></url>'."\n";
        }
        $body .= '</urlset>';

        return response($body, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
