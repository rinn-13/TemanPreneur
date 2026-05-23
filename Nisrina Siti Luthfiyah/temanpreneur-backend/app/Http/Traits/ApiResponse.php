<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Success response
     */
    protected function successResponse($data = null, $message = 'Success', $statusCode = 200, $meta = null): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     */
    protected function errorResponse($message = 'Error', $statusCode = 400, $errors = null, $data = null): JsonResponse
    {
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
     * Paginated response
     */
    protected function paginatedResponse($items, $message = 'Success', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ]
        ], $statusCode);
    }

    /**
     * Created response
     */
    protected function createdResponse($data, $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Updated response
     */
    protected function updatedResponse($data, $message = 'Resource updated successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 200);
    }

    /**
     * Deleted response
     */
    protected function deletedResponse($message = 'Resource deleted successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], 200);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse($errors, $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse($message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse($message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse($message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Server error response
     */
    protected function serverErrorResponse($message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }
}
