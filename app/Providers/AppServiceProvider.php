<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\PersonalAccessClient;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $url = parse_url(URL::temporarySignedRoute(
                'auth.email-verification.verify',
                Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            ));
            $paths = explode('/', $url['path']);

            return $url['scheme'].'://'.$url['host'].($url['port'] ?? null).'/'.$paths[2].'?id='.$paths[3].'&hash='.$paths[4].'&'.$url['query'];
        });
        Passport::useClientModel(Client::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
        Passport::hashClientSecrets();
    }
}
