<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ConfirmPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendResetPasswordRequest;
use App\Models\User;
use App\Utils\JsendFormatter;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
            'password' => ['Invalid password.'],
        ]);
    }

    /**
     * Send a password reset link to the user.
     */
    public function sendResetPasswordNotification(SendResetPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return JsendFormatter::success(null);
        }

        throw ValidationException::withMessages([
            'credentials' => ['Invalid credentials.'],
        ]);
    }

    /**
     * Reset password of the user.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ]);

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return JsendFormatter::success(null);
        }

        throw ValidationException::withMessages([
            'credentials' => ['Invalid credentials.'],
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

    /**
     * Verify the email of the user.
     */
    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();

        return JsendFormatter::success(null);
    }
}
