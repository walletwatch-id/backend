<?php

namespace App\Http\Controllers\OAuth;

use App\Utils\JsendFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Passport\Bridge\User;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Exceptions\AuthenticationException;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthorizationController
{
    /**
     * The authorization server.
     *
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $server;

    /**
     * The authorization view response implementation.
     *
     * @var \Laravel\Passport\Contracts\AuthorizationViewResponse
     */
    protected $response;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthorizationServer $server,
        AuthorizationViewResponse $response)
    {
        $this->server = $server;
        $this->response = $response;
    }

    /**
     * Authorize a client to access the user's account.
     *
     * @return \Illuminate\Http\Response|\Laravel\Passport\Contracts\AuthorizationViewResponse
     */
    public function __invoke(ServerRequestInterface $psrRequest,
        Request $request,
        ClientRepository $clients,
        TokenRepository $tokens)
    {
        $authRequest = $this->withErrorHandling(function () use ($psrRequest) {
            return $this->server->validateAuthorizationRequest($psrRequest);
        });

        if (! Auth::guard('web')->check()) {
            return $request->get('prompt') === 'none'
                    ? $this->denyRequest($authRequest)
                    : $this->promptForLogin($request);
        }

        if ($request->get('prompt') === 'login' &&
            ! $request->session()->get('promptedForLogin', false)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->promptForLogin($request);
        }

        $request->session()->forget('promptedForLogin');

        $scopes = $this->parseScopes($authRequest);
        $user = Auth::guard('web')->user();
        $client = $clients->find($authRequest->getClient()->getIdentifier());

        if ($request->get('prompt') !== 'consent' &&
            ($client->skipsAuthorization() || $this->hasValidToken($tokens, $user, $client, $scopes))) {
            return $this->approveRequest($authRequest, $user);
        }

        if ($request->get('prompt') === 'none') {
            return $this->denyRequest($authRequest, $user);
        }

        $request->session()->put('authToken', $authToken = Str::random());
        $request->session()->put('authRequest', $authRequest);

        return JsendFormatter::success([
            'client' => $client,
            'user' => $user,
            'scopes' => $scopes,
            'request' => $request,
            'auth_token' => $authToken,
        ]);
    }

    /**
     * Transform the authorization requests's scopes into Scope instances.
     *
     * @param  \League\OAuth2\Server\RequestTypes\AuthorizationRequest  $authRequest
     * @return array
     */
    protected function parseScopes($authRequest)
    {
        return Passport::scopesFor(
            collect($authRequest->getScopes())->map(function ($scope) {
                return $scope->getIdentifier();
            })->unique()->all()
        );
    }

    /**
     * Determine if a valid token exists for the given user, client, and scopes.
     *
     * @param  \Laravel\Passport\TokenRepository  $tokens
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  \Laravel\Passport\Client  $client
     * @param  array  $scopes
     * @return bool
     */
    protected function hasValidToken($tokens, $user, $client, $scopes)
    {
        $token = $tokens->findValidToken($user, $client);

        return $token && $token->scopes === collect($scopes)->pluck('id')->all();
    }

    /**
     * Perform the given callback with exception handling.
     *
     * @param  \Closure  $callback
     * @return mixed
     *
     * @throws \Laravel\Passport\Exceptions\OAuthServerException
     */
    protected function withErrorHandling($callback)
    {
        try {
            return $callback();
        } catch (OAuthServerException $e) {
            throw new HttpException(
                $e->getHttpStatusCode(),
                $e->getMessage(),
                $e,
                $e->getHttpHeaders(),
            );
        }
    }

    /**
     * Approve the authorization request.
     *
     * @param  \League\OAuth2\Server\RequestTypes\AuthorizationRequest  $authRequest
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return \Illuminate\Http\Response
     */
    protected function approveRequest($authRequest, $user)
    {
        $authRequest->setUser(new User($user->getAuthIdentifier()));

        $authRequest->setAuthorizationApproved(true);

        return $this->withErrorHandling(function () use ($authRequest) {
            $response = $this->server->completeAuthorizationRequest($authRequest, new Psr7Response);

            return JsendFormatter::success([
                'redirect' => $response->getHeaders()['Location'][0],
            ]);
        });
    }

    /**
     * Deny the authorization request.
     *
     * @param  \League\OAuth2\Server\RequestTypes\AuthorizationRequest  $authRequest
     * @param  \Illuminate\Contracts\Auth\Authenticatable|null  $user
     * @return \Illuminate\Http\Response
     */
    protected function denyRequest($authRequest, $user = null)
    {
        if (is_null($user)) {
            $uri = $authRequest->getRedirectUri()
                ?? (is_array($authRequest->getClient()->getRedirectUri())
                    ? $authRequest->getClient()->getRedirectUri()[0]
                    : $authRequest->getClient()->getRedirectUri());

            $separator = $authRequest->getGrantTypeId() === 'implicit' ? '#' : '?';

            $uri = $uri.(str_contains($uri, $separator) ? '&' : $separator).'state='.$authRequest->getState();

            $response = OAuthServerException::accessDenied('Unauthenticated', $uri)->generateHttpResponse(new Psr7Response);

            return JsendFormatter::success([
                'redirect' => $response->getHeaders()['Location'][0],
            ]);
        }

        $authRequest->setUser(new User($user->getAuthIdentifier()));

        $authRequest->setAuthorizationApproved(false);

        return $this->withErrorHandling(function () use ($authRequest) {
            $response = $this->server->completeAuthorizationRequest($authRequest, new Psr7Response);

            return JsendFormatter::success([
                'redirect' => $response->getHeaders()['Location'][0],
            ]);
        });
    }

    /**
     * Prompt the user to login by throwing an AuthenticationException.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @throws \Laravel\Passport\Exceptions\AuthenticationException
     */
    protected function promptForLogin($request)
    {
        $request->session()->put('promptedForLogin', true);

        throw new AuthenticationException;
    }
}
