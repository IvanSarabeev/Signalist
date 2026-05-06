<?php

declare(strict_types=1);

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ApiResponse
 *
 * Provides a standardized JSON response structure for the entire API.
 *
 * This ensures consistency across all endpoints and simplifies frontend integration.
 *
 * Standard response format:
 * {
 *     "status": bool,
 *     "data": mixed,
 *     "errors": string[],
 *     "meta": array
 * }
 *
 * @package App\Response
 */
final readonly class ApiResponse
{
    /**
     * Create a successful JSON response.
     *
     * Used when the request completes successfully and optionally returns data.
     *
     * Example:
     *  ApiResponse::success($userDto);
     *
     * @param array<int, mixed>|object|null $data The main response payload (DTO, array, scalar, or null)
     * @param array<string, mixed> $meta Optional metadata (e.g. pagination, filters)
     * @param int $status HTTP status code (default: 200 OK)
     *
     * @return JsonResponse Structured success response
     */
    public static function success(
        mixed $data = null,
        array $meta = [],
        int $status = Response::HTTP_OK
    ): JsonResponse {
        return new JsonResponse([
            'status' => true,
            'data' => $data,
            'errors' => [],
            'meta' => $meta,
        ], $status);
    }

    /**
     * Create an error JSON response.
     *
     * Used when the request fails due to validation, business logic,
     * or unexpected exceptions.
     *
     * Example:
     *  ApiResponse::error('Invalid credentials', 401);
     *
     * @param string $message Primary error message (human-readable)
     * @param int $status HTTP status code (default: 400 Bad Request)
     * @param array<string> $errors Additional error details (optional)
     *
     * @return JsonResponse Structured error response
     */
    public static function error(
        string $message,
        int $status   = Response::HTTP_BAD_REQUEST,
        array $errors = [],
    ): JsonResponse {
        return new JsonResponse([
            'status' => false,
            'data' => null,
            'errors' => array_merge([$message], $errors),
            'meta' => [],
        ], $status);
    }

    /**
     * Create a collection response.
     *
     * Intended for returning lists of resources (e.g. users, stocks, transactions).
     *
     * This is a semantic wrapper around the success response for readability.
     *
     * Example:
     *  ApiResponse::collection($userDtos);
     *
     * @param array<int, mixed> $data List of items (typically DTOs)
     * @param array<int, mixed> $meta List of meta data (typically pagination)
     *
     * @return JsonResponse Structured collection response
     */
    public static function collection(array $data, array $meta = []): JsonResponse
    {
        return self::success($data, $meta);
    }
}
