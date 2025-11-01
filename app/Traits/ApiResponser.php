<?php

namespace App\Traits;

use App\Enums\ResponseStatus;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

trait ApiResponser
{
    /**
     * ✅ Réponse standardisée pour succès
     */
    protected function successResponse(
        mixed $data = null,
        string $message = "Opération réussie",
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return $this->formatResponse(ResponseStatus::SUCCESS, $data, $message, $code);
    }

    /**
     * ❌ Réponse standardisée pour erreur
     */
    protected function errorResponse(
        string $message = "Erreur interne",
        int $code = Response::HTTP_BAD_REQUEST,
        mixed $errors = null
    ): JsonResponse {
        $response = $this->formatResponse(ResponseStatus::ECHEC, null, $message, $code);
        if (!is_null($errors)) {
            $data = $response->getData(true);
            $data['errors'] = $errors;
            return response()->json($data, $code);
        }
        return $response;
    }

    /**
     * 🚫 Réponse standardisée pour accès refusé
     */
    protected function deniedResponse(
        string $message = "Accès refusé",
        int $code = Response::HTTP_FORBIDDEN,
        mixed $errors = null
    ): JsonResponse {
        $response = $this->formatResponse(ResponseStatus::ECHEC, null, $message, $code);
        if (!is_null($errors)) {
            $data = $response->getData(true);
            $data['errors'] = $errors;
            return response()->json($data, $code);
        }
        return $response;
    }

    /**
     * � Méthode générique pour renvoyer une réponse personnalisée
     */
    protected function sendResponse(
        mixed $data,
        string $message = "Opération réussie",
        int $code = Response::HTTP_OK,
        ResponseStatus $status = ResponseStatus::SUCCESS
    ): JsonResponse {
        return $this->formatResponse($status, $data, $message, $code);
    }

    /**
     * 🧩 Méthode centrale de formatage de la réponse API
     */
    private function formatResponse(
        ResponseStatus $status,
        mixed $data,
        string $message,
        int $code
    ): JsonResponse {
        // ✅ Cas 1 : Resource::collection avec pagination
        if ($data instanceof AnonymousResourceCollection && $data->resource instanceof LengthAwarePaginator) {
            $paginator = $data->resource;

            return response()->json([
                'status'     => $status->value,
                'http_code'  => $code,
                'message'    => $message,
                'data'       => $data->collection, // garde la transformation de la Resource
                'pagination' => $this->formatPagination($paginator),
                'links'      => $this->formatLinks($paginator),
            ], $code);
        }

        // ✅ Cas 2 : Pagination classique
        if ($data instanceof LengthAwarePaginator) {
            return response()->json([
                'status'     => $status->value,
                'http_code'  => $code,
                'message'    => $message,
                'data'       => $data->items(),
                'pagination' => $this->formatPagination($data),
                'links'      => $this->formatLinks($data),
            ], $code);
        }

        // ✅ Cas 3 : Collection simple
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        // ✅ Cas 4 : Réponse simple sans pagination
        return response()->json([
            'status'    => $status->value,
            'http_code' => $code,
            'message'   => $message,
            'data'      => $data ?? [],
        ], $code);
    }

    /**
     * 📄 Formatage des données de pagination
     */
    private function formatPagination(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage'  => $paginator->currentPage(),
            'totalPages'   => $paginator->lastPage(),
            'totalItems'   => $paginator->total(),
            'itemsPerPage' => $paginator->perPage(),
            'hasNext'      => $paginator->hasMorePages(),
            'hasPrevious'  => $paginator->currentPage() > 1,
        ];
    }

    /**
     * 🔗 Formatage des liens de pagination
     */
    private function formatLinks(LengthAwarePaginator $paginator): array
    {
        return [
            'self'  => $paginator->url($paginator->currentPage()),
            'next'  => $paginator->nextPageUrl(),
            'prev'  => $paginator->previousPageUrl(),
            'first' => $paginator->url(1),
            'last'  => $paginator->url($paginator->lastPage()),
        ];
    }
}
