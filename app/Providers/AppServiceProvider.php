<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\PersonalAccessClient;
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
        Passport::useClientModel(Client::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
        Passport::hashClientSecrets();
    }
}
