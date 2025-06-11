<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;

class AuthenticateApi extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            return parent::handle($request, $next, ...$guards);
        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'errors' => 'Invalid or expired authentication token'
            ], 401);
        }
    }

    // Add this to prevent redirects
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'errors' => 'Invalid or expired authentication token'
            ], 401);
        }
    }
}