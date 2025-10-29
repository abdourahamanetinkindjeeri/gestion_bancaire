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
     * Personnalise la réponse pour les erreurs de validation API
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $response = [
                'status'    => 'error',
                'http_code' => 422,
                'message'   => 'Les données fournies ne sont pas valides',
                'errors'    => $exception->errors(),
            ];
            return response()->json($response, 422);
        }
        return parent::render($request, $exception);
    }
}
