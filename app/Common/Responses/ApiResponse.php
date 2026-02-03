<?php

namespace App\Common\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function make(
        int $code,
        string $message,
        $data = null,
        $errors = null,
        array $meta = null
    ): JsonResponse {
        $response = array_filter(
            [
                'message' => $message,
                'data'    => $data,
                'errors'  => $errors,
                'meta'    => $meta,
            ],
            fn($value) => !is_null($value)
        );

        return response()->json($response, $code);
    }

    public static function success(string $message, $data = null, int $code = 200, array $meta = null): JsonResponse
    {
        return self::make($code, $message, $data, null, $meta);
    }

    public static function successData($data = null, int $code = 200, array $meta = null): JsonResponse
    {
        return self::make($code, __('OK'), $data, null, $meta);
    }

    public static function created($data = null, string $message = 'Created'): JsonResponse
    {
        return self::success($message, $data, 201);
    }

    public static function error(string $message, int $code, $errors = null): JsonResponse
    {
        return self::make($code, $message, null, $errors);
    }

    public static function validation($errors): JsonResponse
    {
        return self::error(__('Validation errors'), 422, $errors);
    }

    public static function InternalServerError($exceptionMsg = null): JsonResponse
    {
        return self::error(
            $exceptionMsg ?? __('Internal Server Error'),
            500
        );
    }
}
