<?php

use App\Utils\JsendFormatter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return JsendFormatter::success([
        'message' => config('api.root_welcome_message'),
        'core_version' => config('app.version'),
    ]);
});

Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        return JsendFormatter::success([
            'message' => config('api.v1_welcome_message'),
            'core_version' => config('app.version'),
            'api_version' => config('api.v1_version'),
            'documentation' => config('api.v1_documentation'),
        ]);
    });
});
