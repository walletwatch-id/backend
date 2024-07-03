<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Utils\Encoder;
use App\Utils\JsendFormatter;
use App\Utils\Storage;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Register a new user.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
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

        Auth::guard('web')->login($user);

        return JsendFormatter::success(null, 204);
    }
}
