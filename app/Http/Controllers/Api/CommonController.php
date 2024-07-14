<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utils\JsendFormatter;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;

class CommonController extends Controller
{
    /**
     * Check the health of the application.
     */
    public function checkHealth(): JsonResponse
    {
        Event::dispatch(new DiagnosingHealth);

        if (defined('LARAVEL_START')) {
            $latency = round((microtime(true) - LARAVEL_START) * 1000);

            return JsendFormatter::success([
                'message' => 'Response successfully rendered in '.$latency.'ms',
                'latency' => $latency,
            ]);
        } else {
            return JsendFormatter::success([
                'message' => 'Server is up and running.',
            ]);
        }
    }
}
