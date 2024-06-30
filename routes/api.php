<?php

use App\Utils\JsendFormatter;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api'], function () {
    Route::get('/', function () {
        return JsendFormatter::success([
            'message' => config('api.root_welcome_message'),
            'core_version' => config('app.version'),
        ]);
    });

    Route::group(['prefix' => 'v1'], function () {
        Route::get('/', function () {
            return JsendFormatter::success([
                'message' => config('api.v1_welcome_message'),
                'core_version' => config('app.version'),
                'api_version' => config('api.v1_version'),
                'documentation' => config('api.v1_documentation'),
            ]);
        });

        Route::apiResources([
            'users' => 'App\Http\Controllers\V1\UserController',
            'paylaters' => 'App\Http\Controllers\V1\PaylaterController',
            'instances' => 'App\Http\Controllers\V1\InstanceController',
            'transactions' => 'App\Http\Controllers\V1\TransactionController',
            'surveys' => 'App\Http\Controllers\V1\SurveyController',
            'chat-sessions' => 'App\Http\Controllers\V1\ChatSessionController',
        ]);
        Route::apiResource('paylaters.hotlines', 'App\Http\Controllers\V1\PaylaterHotlineController')
            ->only(['index', 'store']);
        Route::apiResource('instances.hotlines', 'App\Http\Controllers\V1\InstanceHotlineController')
            ->only(['index', 'store']);
        Route::apiResource('hotlines', 'App\Http\Controllers\V1\HotlineController')
            ->only(['show', 'update', 'destroy']);
        Route::apiResource('chat-sessions.chat-messages', 'App\Http\Controllers\V1\ChatMessageController')
            ->shallow();
        Route::get('blobs/{blob}', 'App\Http\Controllers\V1\BlobController')
            ->name('blobs.show');
    });
});
