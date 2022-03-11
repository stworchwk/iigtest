<?php

namespace App\Http\Controllers\libs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ResponseStructure extends ServiceProvider
{
    public function __construct()
    {
        Log::info('Response Structure Started.');
    }

    public static function response(bool $status = false, int $status_code = 200, string $message = null, array $data = null)
    {
        $http_status_code = [200, 201, 401, 404];
        if (!in_array($status_code, $http_status_code)) {
            $status_code = 200;
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'result' => $data
        ], $status_code);
    }

    public static function fail()
    {
        return response()->json([
            'status' => false,
            'message' => __('system.something_went_wrong'),
            'result' => null
        ], 400);
    }
}
