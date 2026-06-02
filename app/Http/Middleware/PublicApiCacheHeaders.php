<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicApiCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($request->isMethodCacheable() && $response->isSuccessful()) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=15, s-maxage=60, stale-while-revalidate=120'
            );
        }

        return $response;
    }
}
