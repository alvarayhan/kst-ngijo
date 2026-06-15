<?php

namespace App\Http\Traits;

use Carbon\Carbon;

trait IntegrationResponse
{
    /**
     * Format standard success response according to the KST Integration Contract.
     *
     * @param mixed $data Data response payload
     * @param int $status HTTP status code (default: 200)
     * @return \Illuminate\Http\JsonResponse
     */
    protected function integrationSuccess($data, $status = 200)
    {
        return response()->json([
            'timestamp' => Carbon::now()->toIso8601String(),
            'response' => $data,
        ], $status);
    }

    /**
     * Format standard error response according to the KST Integration Contract.
     *
     * @param int $code HTTP status code
     * @param string $message Error message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function integrationError($code, $message)
    {
        return response()->json([
            'timestamp' => Carbon::now()->toIso8601String(),
            'response' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $code);
    }
}
