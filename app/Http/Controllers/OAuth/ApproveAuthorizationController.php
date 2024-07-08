<?php

namespace App\Http\Controllers\OAuth;

use App\Traits\HandlesOAuthErrors;
use App\Utils\JsendFormatter;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\RetrievesAuthRequestFromSession;
use League\OAuth2\Server\AuthorizationServer;
use Nyholm\Psr7\Response as Psr7Response;

class ApproveAuthorizationController
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
     * Approve the authorization request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $this->assertValidAuthToken($request);

        $authRequest = $this->getAuthRequestFromSession($request);

        $authRequest->setAuthorizationApproved(true);

        return $this->withErrorHandling(function () use ($authRequest) {
            $response = $this->server->completeAuthorizationRequest($authRequest, new Psr7Response);

            return JsendFormatter::success([
                'redirect' => $response->getHeaders()['Location'][0],
            ]);
        });
    }
}
