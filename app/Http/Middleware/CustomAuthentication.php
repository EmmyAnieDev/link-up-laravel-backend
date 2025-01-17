<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class CustomAuthentication extends Middleware
{
    /**
     * Handle unauthenticated requests.
     *
     * @param Request $request
     * @param  array  $guards
     * @return mixed
     */
    protected function unauthenticated($request, array $guards)
    {
        abort(response()->json([
            'message' => 'unauthorized',
            'success' => false,
            'status' => 401
        ], 401));
    }
}
