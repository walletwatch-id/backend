<?php

use App\Http\Controllers\OAuthController;
use App\Utils\JsendFormatter;
use Illuminate\Support\Facades\Route;

Route::group([
    'as' => 'auth.',
    'prefix' => 'auth',
    'namespace' => 'App\Http\Controllers',
    'middleware' => ['web'],
], function () {
    Route::post('register', 'AuthController@register')
        ->name('register');
    Route::post('login', 'AuthController@login')
        ->name('login');
    Route::post('logout', 'AuthController@logout')
        ->name('logout');
    Route::post('confirm-password', 'AuthController@confirmPassword')
        ->name('password-confirmation');
    Route::post('reset-password/notify', 'AuthController@sendResetPasswordNotification')
        ->name('password-reset.notify');
    Route::post('reset-password', 'AuthController@resetPassword')
        ->name('password-reset');
    Route::post('verify-email/notify', 'AuthController@sendEmailVerificationNotification')
        ->middleware(['auth:web,api'])
        ->name('email-verification.notify');
    Route::post('verify-email/{id}/{hash}', 'AuthController@verifyEmail')
        ->middleware(['auth:web,api', 'signed'])
        ->name('verification.verify');
    Route::get('/user', 'AuthController@user')
        ->middleware(['auth:web,api'])
        ->name('user');
    Route::get('/token', 'AuthController@csrfToken')
        ->name('csrf-token');
});

Route::group([
    'as' => 'passport.',
    'prefix' => 'oauth',
    'namespace' => 'Laravel\Passport\Http\Controllers',
], function () {
    Route::post('/token', 'AccessTokenController@issueToken')
        ->middleware(['throttle'])
        ->name('token');

    Route::get('/authorize', [OAuthController::class, 'authorize'])
        ->middleware(['web'])
        ->name('authorizations.authorize');

    $guard = config('passport.guard') ? 'auth:'.config('passport.guard') : 'auth';

    Route::group(['middleware' => ['web', $guard]], function () {

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

        Route::group([
            'namespace' => 'App\Http\Controllers\Api\V1',
            'middleware' => ['auth:api']
        ], function () {
            Route::apiResources([
                'users' => 'UserController',
                'paylaters' => 'PaylaterController',
                'instances' => 'InstanceController',
                'transactions' => 'TransactionController',
                'surveys' => 'SurveyController',
                'chat-sessions' => 'ChatSessionController',
            ]);
            Route::apiResource('paylaters.hotlines', 'PaylaterHotlineController')
                ->only(['index', 'store']);
            Route::apiResource('instances.hotlines', 'InstanceHotlineController')
                ->only(['index', 'store']);
            Route::apiResource('hotlines', 'HotlineController')
                ->only(['show', 'update', 'destroy']);
            Route::apiResource('chat-sessions.chat-messages', 'ChatMessageController')
                ->shallow();
            Route::get('blobs/{blob}', 'BlobController')
                ->name('blobs.show');
        });
    });
});
