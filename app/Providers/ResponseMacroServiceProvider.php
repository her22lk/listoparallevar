<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Response::macro('success', function ($data = null, $message = 'Success', $statusCode = 200) {
            $response = [
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ];
            
            return response()->json($response, $statusCode);
        });
        Response::macro('error', function ($message = 'Error', $statusCode = 500) {
            $response = [
                'status' => 'error',
                'message' => $message,
            ];

            return Response::json($response, $statusCode);
        });
    }

}