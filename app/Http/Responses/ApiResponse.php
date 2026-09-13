<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success.',
        int $status = 200,
        ?array $meta = null,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data instanceof JsonResource || $data instanceof ResourceCollection
                ? $data->resolve()
                : $data,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    public static function paginated(
        AbstractPaginator $paginator,
        string $message = 'Success.',
        ?callable $transform = null,
    ): JsonResponse {
        $items = $paginator->getCollection();

        if ($transform) {
            $items = $items->map($transform);
        }

        return self::success(
            data: $items->values(),
            message: $message,
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
        );
    }

    public static function error(
        string $message,
        int $status = 400,
        ?array $errors = null,
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
