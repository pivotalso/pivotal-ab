<?php

namespace pivotalso\PivotalAb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use pivotalso\PivotalAb\Facades\Ab;
use Symfony\Component\HttpFoundation\Response;

class PivotalAbMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        Ab::initUser($request);

        $response = $next($request);

        $cookie = Ab::saveSession();

        if (method_exists($response, 'withCookie') && $cookie) {
            return $response->withCookie(cookie()->forever(config('laravel-ab.cache_key'), $cookie));
        }

        return $response;
    }
}
