<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $role = $request->user()?->role?->slug;

        abort_unless($role && in_array($role, $roles, true), 403, 'Accès non autorisé.');

        return $next($request);
    }
}
