<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class CustomAuthentication extends Middleware
{
    /**
     * Handle unauthenticated requests.
     *
     * @param \Illuminate\Http\Request $request
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

    /**
     * Handle the request and authenticate using Sanctum.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, ['sanctum']); // Specify the Sanctum guard
        return $next($request);
    }
}
