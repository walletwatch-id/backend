<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Repositories\StorageFacade;
use App\Utils\Encoder;
use App\Utils\JsendFormatter;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function __construct(
        protected StorageFacade $storageFacade,
    ) {}

    /**
     * Register a new user.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $hasPicture = $request->hasFile('picture');

        if ($hasPicture) {
            $id = $this->storageFacade->store($request->file('picture'), 'avatar');
            $encodedManifest = Encoder::base64UrlEncode($id);
        }

        $user = new User(
            $hasPicture
            ? array_replace($request->validated(), ['picture' => $encodedManifest])
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
