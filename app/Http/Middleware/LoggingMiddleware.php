<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Logger uniquement les opérations de création de compte
        if ($request->isMethod('post') && $request->is('api/v1/comptes')) {
            $this->logOperation($request, $response);
        }

        return $response;
    }

    private function logOperation(Request $request, Response $response)
    {
        $logData = [
            'date_heure' => now()->toISOString(),
            'host' => $request->getHost(),
            'nom_operation' => 'Création de compte',
            'ressource' => 'comptes',
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ];

        Log::channel('operations')->info('Opération de création de compte', $logData);
    }
}
