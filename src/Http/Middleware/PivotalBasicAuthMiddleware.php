<?php

namespace pivotalso\PivotalAb\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PivotalBasicAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = config('laravel-ab.report_username');
        $password = config('laravel-ab.report_password');

        if ($request->getUser() !== $username || $request->getPassword() !== $password) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Access denied"',
                'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
            ]);
        }

        return $next($request);
    }
}
