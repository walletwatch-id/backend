<?php

namespace App\Http\Controllers\OAuth;

use App\Traits\HandlesOAuthErrors;
use App\Utils\JsendFormatter;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\RetrievesAuthRequestFromSession;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as Psr7Response;

class DenyAuthorizationController
{
    use HandlesOAuthErrors, RetrievesAuthRequestFromSession;

    /**
     * The authorization server.
     *
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $server;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(AuthorizationServer $server)
    {
        $this->server = $server;
    }

    /**
     * Deny the authorization request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $this->assertValidAuthToken($request);

        $authRequest = $this->getAuthRequestFromSession($request);

        $authRequest->setAuthorizationApproved(false);

        return $this->withErrorHandling(function () use ($authRequest) {
            try {
                $response = $this->server->completeAuthorizationRequest($authRequest, new Psr7Response);
            } catch (OAuthServerException $e) {
                $response = $e->generateHttpResponse(new Psr7Response);
            }

            return JsendFormatter::success([
                'redirect' => $response->getHeaders()['Location'][0],
            ]);
        });
    }
}
