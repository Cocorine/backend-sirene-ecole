<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait JsonResponseTrait
{
    /**
     * @param mixed|null $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function jsonResponse($data = null, int $status = 200, array $headers = []): JsonResponse
    {
        return response()->json($data, $status, $headers);
    }

    /**
     * @param string|null $message
     * @param mixed|null $data
     * @param int $status
     * @return JsonResponse
     */
    public function successResponse(string $message = null, $data = null, int $status = 200): JsonResponse
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * @param string|null $message
     * @param int $status
     * @return JsonResponse
     */
    public function errorResponse(string $message = null, int $status = 400, array $errors = []): JsonResponse
    {
        Log::error($message, ['status' => $status, 'errors' => $errors]);
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors,

        ], $status);
    }

    /**
     * @param null $data
     * @param string|null $message
     * @return JsonResponse
     */
    public function createdResponse($data = null, string $message = null): JsonResponse
    {
        return $this->successResponse($message ?? 'Resource created successfully.', $data, 201);
    }

    /**
     * @param string|null $message
     * @return JsonResponse
     */
    public function noContentResponse(string $message = null): JsonResponse
    {
        return $this->successResponse($message ?? 'Action performed successfully.', null, 204);
    }

    /**
     * @param string|null $message
     * @return JsonResponse
     */
    public function validationErrorResponse(string $message = 'Validation failed.', array $errors = []): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * @param string|null $message
     * @return JsonResponse
     */
    public function notFoundResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? 'Resource not found.', 404);
    }

    /**
     * @param string|null $message
     * @return JsonResponse
     */
    public function unauthorizedResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? 'Unauthorized.', 401);
    }

    /**
     * @param string|null $message
     * @return JsonResponse
     */
    public function forbiddenResponse(string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? 'Forbidden.', 403);
    }
}
