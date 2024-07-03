<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Utils\JsendFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CsrfTokenController extends Controller
{
    /**
     * Get the CSRF token.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return JsendFormatter::success([
            'token' => $request->session()->token(),
        ]);
    }
}
