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
    protected function successResponse(
        mixed $data = null,
        string $message = "Opération réussie",
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return $this->formatResponse(ResponseStatus::SUCCESS, $data, $message, $code);
    }

    protected function errorResponse(
        string $message = "Erreur interne",
        int $code = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        return $this->formatResponse(ResponseStatus::ECHEC, null, $message, $code);
    }

    protected function sendResponse(
        mixed $data,
        string $message = "Opération réussie",
        int $code = Response::HTTP_OK,
        ResponseStatus $status = ResponseStatus::SUCCESS
    ): JsonResponse {
        return $this->formatResponse($status, $data, $message, $code);
    }

    private function formatResponse(
        ResponseStatus $status,
        mixed $data,
        string $message,
        int $code
    ): JsonResponse {
        // ✅ Cas 1 : Resource::collection avec pagination
        if ($data instanceof AnonymousResourceCollection && $data->resource instanceof LengthAwarePaginator) {
            $paginator = $data->resource;

            $pagination = [
                'currentPage'  => $paginator->currentPage(),
                'totalPages'   => $paginator->lastPage(),
                'totalItems'   => $paginator->total(),
                'itemsPerPage' => $paginator->perPage(),
                'hasNext'      => $paginator->hasMorePages(),
                'hasPrevious'  => $paginator->currentPage() > 1,
            ];

            $links = [
                'self'  => $paginator->url($paginator->currentPage()),
                'next'  => $paginator->nextPageUrl(),
                'prev'  => $paginator->previousPageUrl(),
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
            ];

            return response()->json([
                'status'     => $status->value,
                'http_code'  => $code,
                'message'    => $message,
                'data'       => $data->collection, // garde la transformation Resource
                'pagination' => $pagination,
                'links'      => $links,
            ], $code);
        }

        // ✅ Cas 2 : Paginator classique
        if ($data instanceof LengthAwarePaginator) {
            $pagination = [
                'currentPage'  => $data->currentPage(),
                'totalPages'   => $data->lastPage(),
                'totalItems'   => $data->total(),
                'itemsPerPage' => $data->perPage(),
                'hasNext'      => $data->hasMorePages(),
                'hasPrevious'  => $data->currentPage() > 1,
            ];

            $links = [
                'self'  => $data->url($data->currentPage()),
                'next'  => $data->nextPageUrl(),
                'prev'  => $data->previousPageUrl(),
                'first' => $data->url(1),
                'last'  => $data->url($data->lastPage()),
            ];

            return response()->json([
                'status'     => $status->value,
                'http_code'  => $code,
                'message'    => $message,
                'data'       => $data->items(),
                'pagination' => $pagination,
                'links'      => $links,
            ], $code);
        }

        // ✅ Cas 3 : Collection simple
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        // ✅ Cas 4 : Réponse simple
        return response()->json([
            'status'    => $status->value,
            'http_code' => $code,
            'message'   => $message,
            'data'      => $data ?? [],
        ], $code);
    }
}
