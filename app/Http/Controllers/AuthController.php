<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ConfirmPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendResetPasswordRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use App\Utils\Encoder;
use App\Utils\JsendFormatter;
use App\Utils\Storage;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
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
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        if ($request->hasFile('picture')) {
            $id = Storage::store($request->file('picture'), 'avatar');
            $encoded_id = Encoder::base64UrlEncode($id);
        }

        $user = new User(
            $request->hasFile('picture')
            ? array_replace($request->validated(), ['picture' => $encoded_id])
            : $request->validated()
        );
        $user->forceFill([
            'password' => $request->password,
            'role' => 'USER',
        ]);
        $user->save();

        event(new Registered($user));

        Auth::login($user);

        return JsendFormatter::success(null, 204);
    }

    /**
     * Login with credentials.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (Auth::attempt($request->validated())) {
            $request->session()->regenerate();

            return JsendFormatter::success(null, 204);
        }

        throw ValidationException::withMessages([
            'credentials' => ['Invalid email or password.'],
        ]);
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return JsendFormatter::success(null, 204);
    }

    /**
     * Get CSRF token.
     */
    public function csrfToken(Request $request): JsonResponse
    {
        return JsendFormatter::success([
            'token' => $request->session()->token(),
        ]);
    }

    /**
     * Confirm password of the authenticated user.
     */
    public function confirmPassword(ConfirmPasswordRequest $request): JsonResponse
    {
        if (Hash::check($request->password, $request->user()->password)) {
            return JsendFormatter::success(null, 204);
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
            return JsendFormatter::success(null, 204);
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
            return JsendFormatter::success(null, 204);
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

        return JsendFormatter::success(null, 204);
    }

    /**
     * Verify the email of the user.
     */
    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();

        return JsendFormatter::success(null, 204);
    }
}
