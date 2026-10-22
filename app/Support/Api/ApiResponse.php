<?php

namespace App\Support\Api;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Phase 12 — Part 15: API Foundation.
 *
 * Consistent response envelopes, pagination (length-aware and cursor),
 * filtering/sorting parameter conventions, and OpenAPI-ready error
 * envelopes for all versioned API responses.
 */
class ApiResponse
{
    /** Success envelope. */
    public static function success(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data instanceof JsonResource ? $data->resolve() : $data,
            'meta' => $meta,
        ], $status);
    }

    /** Error envelope (OpenAPI-friendly shape). */
    public static function error(string $code, string $message, array $errors = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'errors' => $errors,
            ],
        ], $status);
    }

    /** Length-aware paginator envelope. */
    public static function paginated(LengthAwarePaginator $paginator): JsonResponse
    {
        return self::success($paginator->items(), [
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /** Cursor paginator envelope (stable under inserts; preferred for feeds). */
    public static function cursorPaginated(CursorPaginator $paginator): JsonResponse
    {
        return self::success($paginator->items(), [
            'pagination' => [
                'has_more' => $paginator->hasMorePages(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
            ],
        ]);
    }

    /** Extract standard filter/sort parameters from a request. */
    public static function queryOptions(\Illuminate\Http\Request $request, array $allowedFilters = [], array $allowedSorts = []): array
    {
        return [
            'filters' => collect($allowedFilters)
                ->mapWithKeys(fn (string $field) => [$field => $request->query($field)])
                ->filter(fn ($value) => $value !== null)
                ->all(),
            'sort' => in_array(ltrim((string) $request->query('sort'), '-'), $allowedSorts, true)
                ? $request->query('sort')
                : null,
            'per_page' => min(max((int) $request->query('per_page', 15), 1), 100),
        ];
    }
}