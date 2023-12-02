<?php

namespace eighttworules\LaravelAb\Http\Middleware;

use Closure;
use eighttworules\LaravelAb\Facades\Ab;
use Illuminate\Http\Request;

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
