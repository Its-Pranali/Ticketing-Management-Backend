<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddAuthTokenHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->bearerToken() && $request->hasCookie('auth_accessToken')) {
            $token = $request->cookie('auth_accessToken');
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
        return $next($request);
    }
}
