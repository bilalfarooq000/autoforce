<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Without this, a page visited before a deploy can come back blank (or stale)
 * on browser back/forward — the browser restores it straight from bfcache
 * instead of re-requesting it, and that cached HTML references a previous
 * deploy's hashed JS/CSS assets. no-store disables that for every response.
 */
class PreventStaleCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->ajax()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
