<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'as' => 'auth.',
    'prefix' => 'auth',
    'namespace' => 'App\Http\Controllers\Auth',
    'middleware' => ['web'],
], function () {
    Route::post('register', 'RegisterController')
        ->name('register');
    Route::post('login', 'LoginController')
        ->name('login');
    Route::post('logout', 'LogoutController')
        ->middleware(['auth:web'])
        ->name('logout');
    Route::post('confirm-password', 'ConfirmPasswordController')
        ->middleware(['auth:web,api'])
        ->name('password-confirmation');
    Route::post('reset-password/notify', 'ResetPasswordController@sendNotification')
        ->name('password-reset.notify');
    Route::post('reset-password', 'ResetPasswordController@reset')
        ->name('password-reset');
    Route::post('verify-email/notify', 'EmailVerificationController@sendNotification')
        ->middleware(['auth:web,api'])
        ->name('email-verification.notify');
    Route::post('verify-email/{id}/{hash}', 'EmailVerificationController@verify')
        ->middleware(['auth:web,api', 'signed'])
        ->name('email-verification.verify');
    Route::get('/user', 'UserController')
        ->middleware(['auth:web,api', 'verified'])
        ->name('user');
    Route::get('/token', 'CsrfTokenController')
        ->name('csrf-token');
});

Route::group([
    'as' => 'passport.',
    'prefix' => 'oauth2',
], function () {
    $guard = config('passport.guard') ? 'auth:'.config('passport.guard') : 'auth';

    Route::post('/token', 'Laravel\Passport\Http\Controllers\AccessTokenController@issueToken')
        ->middleware(['throttle'])
        ->name('token');

    Route::get('/_authorize', 'App\Http\Controllers\OAuth\AuthorizationController')
        ->middleware(['web'])
        ->name('authorizations._authorize');

    Route::post('/_authorize', 'App\Http\Controllers\OAuth\ApproveAuthorizationController')
        ->middleware(['web', $guard])
        ->name('authorizations.approve');

    Route::delete('/_authorize', 'App\Http\Controllers\OAuth\DenyAuthorizationController')
        ->middleware(['web', $guard])
        ->name('authorizations.deny');

    Route::get('/authorize', 'App\Http\Controllers\Web\DummyController')
        ->name('authorizations.authorize');

    Route::group([
        'namespace' => 'Laravel\Passport\Http\Controllers',
        'middleware' => ['web', $guard],
    ], function () {
        Route::get('/tokens', 'AuthorizedAccessTokenController@forUser')
            ->name('tokens.index');

        Route::delete('/tokens/{token_id}', 'AuthorizedAccessTokenController@destroy')
            ->name('tokens.destroy');

        Route::get('/clients', 'ClientController@forUser')
            ->name('clients.index');

        Route::post('/clients', 'ClientController@store')
            ->name('clients.store');

        Route::put('/clients/{client_id}', 'ClientController@update')
            ->name('clients.update');

        Route::delete('/clients/{client_id}', 'ClientController@destroy')
            ->name('clients.destroy');

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

Route::group([
    'as' => 'api.',
    'prefix' => 'api',
    'namespace' => 'App\Http\Controllers\Api',
], function () {
    Route::get('/', 'CommonController@greet');
    Route::get('/status', 'CommonController@checkHealth');

    Route::group([
        'as' => 'v1.',
        'prefix' => 'v1',
        'namespace' => 'V1',
    ], function () {
        Route::get('/', 'CommonController@greet');
        Route::get('blobs/{blob}', 'BlobController');

        Route::group(['middleware' => ['auth:api', 'verified']], function () {
            Route::apiResources([
                'users' => 'UserController',
                'paylaters' => 'PaylaterController',
                'instances' => 'InstanceController',
                'transactions' => 'TransactionController',
                'financial-surveys' => 'FinancialSurveyController',
                'personality-surveys' => 'PersonalitySurveyController',
                'chat-sessions' => 'ChatSessionController',
            ], [
                'parameters' => [
                    'financial-surveys' => 'survey',
                    'personality-surveys' => 'survey',
                ],
            ]);
            Route::apiResource('paylaters.hotlines', 'PaylaterHotlineController')
                ->only(['index', 'store']);
            Route::apiResource('instances.hotlines', 'InstanceHotlineController')
                ->only(['index', 'store']);
            Route::apiResource('hotlines', 'HotlineController')
                ->only(['show', 'update', 'destroy']);
            Route::apiResource('financial-surveys.survey-questions', 'SurveyQuestionController')
                ->parameter('financial-surveys', 'survey')
                ->only(['index', 'store']);
            Route::apiResource('personality-surveys.survey-questions', 'SurveyQuestionController')
                ->parameter('personality-surveys', 'survey')
                ->only(['index', 'store']);
            Route::apiResource('survey-questions', 'SurveyQuestionController')
                ->only(['show', 'update', 'destroy']);
            Route::apiResource('financial-surveys.survey-results', 'SurveyResultController')
                ->parameter('financial-surveys', 'survey')
                ->only(['index', 'store']);
            Route::apiResource('personality-surveys.survey-results', 'SurveyResultController')
                ->parameter('personality-surveys', 'survey')
                ->only(['index', 'store']);
            Route::apiResource('survey-results', 'SurveyResultController')
                ->only(['show', 'update', 'destroy']);
            Route::apiResource('survey-results.survey-result-answers', 'SurveyResultAnswerController')
                ->shallow();
            Route::apiResource('statistics', 'StatisticController')
                ->only(['index', 'show']);
            Route::apiResource('chat-sessions.chat-messages', 'ChatMessageController')
                ->shallow();
        });
    });
});
