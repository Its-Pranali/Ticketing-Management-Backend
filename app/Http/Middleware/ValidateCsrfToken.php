<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ValidateCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            in_array($request->method(), ['GET', 'HEAD', 'OPTIONS']) ||
            $request->is('api/logout') ||
            $request->is('api/login') ||
            $request->hasHeader('Authorization')
        ) {
            return $next($request);
        }

        // 2. Check if the X-XSRF-TOKEN header matches the XSRF-TOKEN cookie
        $tokenFromHeader = $request->header('X-XSRF-TOKEN');
        $tokenFromCookie = $request->cookie('XSRF-TOKEN');

        Log::info('CSRF Debug', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'token_from_header' => $tokenFromHeader,
            'token_from_cookie' => $tokenFromCookie,
            'match' => ($tokenFromHeader === $tokenFromCookie)
        ]);

        if (!$tokenFromHeader || !$tokenFromCookie || $tokenFromHeader !== $tokenFromCookie) {
            Log::warning('CSRF validation failed', [
                'has_header' => !empty($tokenFromHeader),
                'has_cookie' => !empty($tokenFromCookie),
                'match' => ($tokenFromHeader === $tokenFromCookie),
                'host' => $request->getHost(),
            ]);

            return response()->json([
                'message' => 'CSRF token mismatch. The action is unauthorized.',
                'tip' => 'Please clear your browser cookies, log out, and log in again to refresh your session.',
                'debug' => [
                    'header_present' => !empty($tokenFromHeader),
                    'cookie_present' => !empty($tokenFromCookie),
                    'match' => ($tokenFromHeader === $tokenFromCookie)
                ]
            ], 419);
        }
        return $next($request);
    }
}
