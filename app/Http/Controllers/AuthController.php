<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ConfirmPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Utils\JsendFormatter;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login with credentials.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (Auth::once($credentials)) {
            // TODO: Implement token generation and return it in the response.

            return JsendFormatter::success();
        }

        throw ValidationException::withMessages([
            'credentials' => ['Invalid email or password.'],
        ]);
    }

    /**
     * Confirm password of the authenticated user.
     */
    public function confirmPassword(ConfirmPasswordRequest $request): JsonResponse
    {
        if (Hash::check($request->password, $request->user()->password)) {
            return JsendFormatter::success(null);
        }

        throw ValidationException::withMessages([
            'password' => ['The provided password does not match.'],
        ]);
    }

    /**
     * Send an email verification link to the authenticated user.
     */
    public function sendEmailVerificationNotification(Request $request): JsonResponse
    {
        $request->user()->sendEmailVerificationNotification();

        return JsendFormatter::success(null);
    }

    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();

        return JsendFormatter::success(null);
    }
}
