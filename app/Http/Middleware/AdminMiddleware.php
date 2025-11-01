<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->admin) {
            return response()->json([
                'status' => 'error',
                'http_code' => 403,
                'message' => 'Accès refusé (admin uniquement)'
            ], 403);
        }
        return $next($request);
    }
}
