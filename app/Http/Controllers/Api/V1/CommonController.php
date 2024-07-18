<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Utils\JsendFormatter;
use Illuminate\Http\JsonResponse;

class CommonController extends Controller
{
    /**
     * Greet the user.
     */
    public function greet(): JsonResponse
    {
        return JsendFormatter::success([
            'message' => config('api.v1_welcome_message'),
            'core_version' => config('app.version'),
            'api_version' => config('api.v1_version'),
            'documentation' => config('api.v1_documentation'),
        ]);
    }
}
