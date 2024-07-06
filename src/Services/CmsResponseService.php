<?php

namespace Grafite\Cms\Services;

use Illuminate\Http\JsonResponse;

class CmsResponseService
{
    /**
     * Generate an api response.
     */
    public function apiResponse(string $type, mixed $message, $code = 200): JsonResponse
    {
        return response()->json(['status' => $type, 'data' => $message], $code);
    }

    /**
     * Generate an API error response.
     */
    public function apiErrorResponse(array $errors, array $inputs): JsonResponse
    {
        $message = [];
        foreach ($inputs as $key => $value) {
            if (! isset($errors[$key])) {
                $message[$key] = [
                    'status' => 'valid',
                    'value' => $value,
                ];
            } else {
                $message[$key] = [
                    'status' => 'invalid',
                    'error' => $errors[$key],
                    'value' => $value,
                ];
            }
        }

        return response()->json(['status' => 'error', 'data' => $message]);
    }
}
