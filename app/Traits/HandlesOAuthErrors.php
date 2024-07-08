<?php

namespace App\Traits;

use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait HandlesOAuthErrors
{
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
}
