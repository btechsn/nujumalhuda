<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Enveloppe de réponse API unifiée.
 *
 * Toutes les réponses API suivent la même structure :
 * {
 *   "success": true|false,
 *   "data": {...}|[...],
 *   "message": "...",
 *   "errors": {...},
 *   "meta": {...}
 * }
 */
class ApiResponse
{
    /**
     * Réponse de succès.
     */
    public static function success(
        mixed $data = null,
        string $message = '',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->additional(array_merge(['success' => true], $meta))
                ->response()
                ->setStatusCode($statusCode);
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Réponse d'erreur.
     */
    public static function error(
        string $message,
        int $statusCode = 400,
        mixed $errors = null,
        mixed $data = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Réponse 404 Not Found.
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Réponse 401 Unauthorized.
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Réponse 403 Forbidden.
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * Réponse 422 Unprocessable Entity (validation).
     */
    public static function validationError(
        string $message = 'Validation failed',
        array $errors = []
    ): JsonResponse {
        return self::error($message, 422, $errors);
    }

    /**
     * Réponse 500 Internal Server Error.
     */
    public static function serverError(
        string $message = 'Internal server error'
    ): JsonResponse {
        return self::error($message, 500);
    }

    /**
     * Réponse 201 Created.
     */
    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return self::success($data, $message, 201);
    }

    /**
     * Réponse 204 No Content.
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
