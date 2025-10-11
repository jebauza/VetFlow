<?php

namespace App\Common\Controllers;

use Illuminate\Http\JsonResponse;

abstract class ApiController
{
    public function sendResponse(string $message = null, $result = null, int $code = 200): JsonResponse
    {
        $response = [
            'message' => $message ?? __('Request processed successfully'),
        ];

        if ($result !== null) {
            $response['data'] = $result;
        }

        return response()->json($response, $code);
    }

    public function sendError(string $message, int $code): JsonResponse
    {
        $response = [
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    public function sendError500($error): JsonResponse
    {
        $response = [
            'message' => __('Internal Server Error'),
            'error' => $error
        ];

        return response()->json($response, 500);
    }

    public function sendError422(array $errors): JsonResponse
    {
        return response()->json($errors, 422);
    }
}
