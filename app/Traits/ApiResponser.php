<?php

namespace App\Traits;

use App\Enums\ResponseStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait ApiResponser
{
    /**
     * Réponse succès standardisée (avec enum ResponseStatus).
     */
    protected function success(
        ?string $message = null,
        $data = null,
        int $code = 200,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'status' => ResponseStatus::SUCCESS->value,
            'message' => $message ?? 'Succès',
            'data' => $data,
            'meta' => $meta,
        ], $code);
    }

    /**
     * Réponse erreur standardisée (avec enum ResponseStatus).
     */
    protected function error(
        ?string $message = null,
        int $code = 400,
        $errors = null,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'status' => ResponseStatus::ECHEC->value,
            'message' => $message ?? 'Erreur',
            'errors' => $errors,
            'meta' => $meta,
        ], $code);
    }

    /**
     * Réponse pour une collection paginée ou simple.
     */
    protected function collectionResponse($collection, ?string $message = null, int $code = 200): JsonResponse
    {
        if ($collection instanceof LengthAwarePaginator) {
            return $this->success(
                $message ?? 'Liste paginée',
                $collection->items(),
                $code,
                [
                    'pagination' => [
                        'total' => $collection->total(),
                        'count' => $collection->count(),
                        'per_page' => $collection->perPage(),
                        'current_page' => $collection->currentPage(),
                        'total_pages' => $collection->lastPage(),
                    ]
                ]
            );
        }
        if ($collection instanceof Collection) {
            return $this->success(
                $message ?? 'Liste',
                $collection,
                $code
            );
        }
        return $this->success($message, $collection, $code);
    }
}
