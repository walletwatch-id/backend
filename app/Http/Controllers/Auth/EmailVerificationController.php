<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Utils\JsendFormatter;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Send an email verification link to the authenticated user.
     */
    public function sendNotification(Request $request): JsonResponse
    {
        $request->user()->sendEmailVerificationNotification();

        return JsendFormatter::success(null, 204);
    }

    /**
     * Verify the email of the user.
     */
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        $request->fulfill();

        return JsendFormatter::success(null, 204);
    }
}
