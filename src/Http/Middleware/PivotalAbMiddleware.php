<?php

namespace pivotalso\PivotalAb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use pivotalso\PivotalAb\Facades\Ab;

class PivotalAbMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Ab::initUser($request);

        $response = $next($request);

        $cookie = Ab::saveSession();
        if (method_exists($response, 'withCookie')) {
            return $response->withCookie(cookie()->forever(config('laravel-ab.cache_key'), $cookie));
        }
        return $response;
    }
}
