<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmPasswordRequest;
use App\Models\User;
use App\Utils\JsendFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordController extends Controller
{
    /**
     * Confirm password of the authenticated user.
     */
    public function __invoke(ConfirmPasswordRequest $request): JsonResponse
    {
        if (Hash::check($request->password, $request->user()->password)) {
            return JsendFormatter::success(null, 204);
        }

        throw ValidationException::withMessages([
            'password' => ['Invalid password.'],
        ]);
    }
}
