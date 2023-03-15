<?php

namespace App\Http\ResponseHelpers;

use \Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function format($data, $message = null, $status = 200, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => $status == 200,
            'data' => $data,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    public static function success($data, $message = null): JsonResponse
    {
        return self::format($data, $message);
    }

    public static function error($message = null, $status = 400, $errors = null): JsonResponse
    {
        return self::format(null, $message, $status, $errors);
    }
}