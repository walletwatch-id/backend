<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Utils\JsendFormatter;
use Illuminate\Http\Request;

class DummyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $message = 'This is a dummy route. You should configure a reverse proxy to serve this route from the front-end application.';

        if ($request->wantsJson()) {
            return JsendFormatter::error(
                'This is a dummy route. You should configure a reverse proxy to serve this route from the front-end application.',
                null,
                null,
                502,
            );
        } else {
            return response($message, 502);
        }
    }
}
