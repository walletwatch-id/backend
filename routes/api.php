<?php

use App\Utils\JsendFormatter;
use Illuminate\Support\Facades\Route;

Route::group([
    'as' => 'passport.',
    'prefix' => 'oauth',
    'namespace' => 'Laravel\Passport\Http\Controllers',
], function () {
    Route::post('/token', 'AccessTokenController@issueToken')
        ->middleware(['throttle'])
        ->name('token');

    $guard = config('passport.guard', null);

    Route::middleware(['web', $guard ? 'auth:'.$guard : 'auth'])->group(function () {
        Route::get('/authorize', 'AuthorizationController@authorize')
            ->name('authorizations.authorize');

        Route::post('/authorize', 'ApproveAuthorizationController@approve')
            ->name('authorizations.approve');

        Route::delete('/authorize', 'DenyAuthorizationController@deny')
            ->name('authorizations.deny');

        Route::post('/token/refresh', 'TransientTokenController@refresh')
            ->name('token.refresh');

        Route::get('/clients', 'ClientController@forUser')
            ->name('clients.index');

        Route::post('/clients', 'ClientController@store')
            ->name('clients.store');

        Route::put('/clients/{client_id}', 'ClientController@update')
            ->name('clients.update');

        Route::delete('/clients/{client_id}', 'ClientController@destroy')
            ->name('clients.destroy');

        Route::get('/tokens', 'AuthorizedAccessTokenController@forUser')
            ->name('tokens.index');

        Route::delete('/tokens/{token_id}', 'AuthorizedAccessTokenController@destroy')
            ->name('tokens.destroy');

        Route::get('/personal-access-tokens', 'PersonalAccessTokenController@forUser')
            ->name('personal-tokens.index');

        Route::post('/personal-access-tokens', 'PersonalAccessTokenController@store')
            ->name('personal-tokens.store');

        Route::delete('/personal-access-tokens/{token_id}', 'PersonalAccessTokenController@destroy')
            ->name('personal-tokens.destroy');

        Route::get('/scopes', 'ScopeController@all')
            ->name('scopes.index');
    });
});

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

        Route::middleware(['auth:api'])->group(function () {
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
});
