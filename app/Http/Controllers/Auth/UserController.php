<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Utils\JsendFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get the authenticated user.
     */
    public function __invoke(Request $request): JsonResponse
    {
        return JsendFormatter::success([
            'user' => $request->user(),
        ]);
    }
}
