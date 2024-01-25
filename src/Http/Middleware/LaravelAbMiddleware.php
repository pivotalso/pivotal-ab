<?php

namespace pivotalso\LaravelAb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use pivotalso\LaravelAb\Facades\Ab;

class LaravelAbMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $cookie = Ab::saveSession();
        if (method_exists($response, 'withCookie')) {
            return $response->withCookie(cookie()->forever(config('laravel-ab.cache_key'), $cookie));
        }

        return $response;
    }
}
