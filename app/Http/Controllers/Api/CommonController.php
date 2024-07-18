<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utils\JsendFormatter;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\JsonResponse;

class CommonController extends Controller
{
    /**
     * Greet the user.
     */
    public function greet(): JsonResponse
    {
        return JsendFormatter::success([
            'message' => config('api.root_welcome_message'),
            'core_version' => config('app.version'),
        ]);
    }

    /**
     * Check the health of the application.
     */
    public function checkHealth(): JsonResponse
    {
        event(new DiagnosingHealth);

        if (defined('LARAVEL_START')) {
            $latency = round((microtime(true) - LARAVEL_START) * 1000);

            return JsendFormatter::success([
                'message' => 'Server is up and running. Response successfully rendered in '.$latency.'ms',
                'latency' => $latency,
            ]);
        } else {
            return JsendFormatter::success([
                'message' => 'Server is up and running.',
            ]);
        }
    }
}
