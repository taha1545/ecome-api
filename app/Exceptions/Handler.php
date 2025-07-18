<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    //
    public function render($request, Throwable $exception)
    {
        //
        if ($request->is('api/*') && $exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'errors' => 'Authentication token is missing or invalid.'
            ], 401);
        }

        return parent::render($request, $exception);
    }
}
