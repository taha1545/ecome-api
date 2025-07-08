<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;

class AuthenticateApi extends Middleware
{
    // 
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $guards = ['sanctum'];
            //
            config(['sanctum.middleware.encrypt_cookies' => false]);
            config(['sanctum.middleware.validate_csrf_token' => false]);

            return parent::handle($request, $next, ...$guards);
        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'errors' => 'Invalid or expired authentication token'
            ], 401);
        }
    }



    protected function redirectTo($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated',
            'errors' => 'Invalid or expired authentication token'
        ], 401);
    }
}
