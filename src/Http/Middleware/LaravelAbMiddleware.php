<?php

namespace eighttworules\LaravelAb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use eighttworules\LaravelAb\Facades\Ab;

class LaravelAbMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
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
