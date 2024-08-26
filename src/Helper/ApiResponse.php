<?php

namespace YajTech\Crud\Helper;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApiResponse
{
    public static function success(string $message = 'Success', int $status_code = 200): JsonResponse
    {
        $data = [
            'message' => $message,
        ];

        return self::response($data, $status_code);
    }

    public static function response($data, $status_code): JsonResponse
    {
        return response()->json($data, $status_code);
    }

    public static function successDirect($data, $status_code = 200): JsonResponse
    {
        return response()->json($data, $status_code);
    }

    public static function successData($data, $message = 'Success', $status_code = 200): JsonResponse
    {
        $data = [
            'message' => $message,
            'data' => $data,
        ];

        return self::response($data, $status_code);
    }

    public static function onException(Exception $exception, string $message = 'Something went wrong')
    {
        if ($exception != null) {
            /** Throw some exceptions when executed from central */
            if (Auth::user() != null) {
                $user = Auth::user();
                $exception_array['User'] = $user->name . ' (' . $user->mobile . ')';
            }
            $exception_array['Error Message'] = $exception->getMessage();
            $exception_array['File'] = "{$exception->getFile()} ({$exception->getLine()})";
            $exception_array['Trace'] = $exception->getTraceAsString();
            Log::error("Error code: {$exception->getCode()}", $exception_array);
            $data = [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ];

            return self::response($data, 500);
        }

        $data = [
            'message' => $message,
            'code' => 500,
        ];

        return self::response($data, 500);
    }

    public static function errorResponse($message, $status_code = 400): JsonResponse
    {
        $data = [
            'code' => $status_code,
            'message' => $message,
        ];

        return self::response($data, $status_code);
    }

    public static function validationError($data, $message = 'Validation error', $status_code = 422): JsonResponse
    {
        $data = [
            'message' => $message,
            'data' => $data,
        ];

        return self::response($data, $status_code);
    }
}
