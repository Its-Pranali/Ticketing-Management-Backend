<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPassportToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?: \Illuminate\Support\Facades\Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated'], 401);
        }

        $token = $user->token();

        if (!$token || $token->revoked || ($token->expires_at && $token->expires_at->isPast())) {
            return response()->json([
                'status' => false,
                'message' => 'Session expired or invalid'
            ], 401);
        }
        return $next($request);
    }
}
