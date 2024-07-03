<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Utils\JsendFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Login with credentials.
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        if (Auth::guard('web')->attempt($request->validated())) {
            $request->session()->regenerate();

            return JsendFormatter::success(null, 204);
        }

        throw ValidationException::withMessages([
            'credentials' => ['Invalid email or password.'],
        ]);
    }
}
