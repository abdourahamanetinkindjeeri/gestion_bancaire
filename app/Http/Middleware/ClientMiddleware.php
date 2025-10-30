<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->client) {
            return response()->json(['message' => 'Accès refusé (client uniquement)'], 403);
        }
        return $next($request);
    }
}
