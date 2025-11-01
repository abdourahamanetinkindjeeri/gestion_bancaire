<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Personnalise la réponse pour les erreurs API
     */
    public function render($request, Throwable $exception)
    {
        // Pour les requêtes API, retourner une réponse JSON formatée
        if ($request->is('api/*') || $request->expectsJson()) {
            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                $response = [
                    'status'    => 'error',
                    'http_code' => 401,
                    'message'   => 'Authentification requise',
                ];
                return response()->json($response, 401);
            }

            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                $response = [
                    'status'    => 'error',
                    'http_code' => 422,
                    'message'   => 'Les données fournies ne sont pas valides',
                    'errors'    => $exception->errors(),
                ];
                return response()->json($response, 422);
            }

            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                $response = [
                    'status'    => 'error',
                    'http_code' => 403,
                    'message'   => $exception->getMessage() ?: 'Accès refusé',
                ];
                return response()->json($response, 403);
            }

            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                $response = [
                    'status'    => 'error',
                    'http_code' => 404,
                    'message'   => 'Ressource introuvable',
                ];
                return response()->json($response, 404);
            }

            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                $response = [
                    'status'    => 'error',
                    'http_code' => 405,
                    'message'   => 'Méthode non autorisée',
                ];
                return response()->json($response, 405);
            }

            // Pour les autres exceptions, retourner une réponse générique
            $response = [
                'status'    => 'error',
                'http_code' => 500,
                'message'   => 'Erreur interne du serveur',
            ];
            return response()->json($response, 500);
        }

        return parent::render($request, $exception);
    }
}
