<?php

namespace App\Providers;

use App\Services\RateProviders\RateProvider;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;

// use App\Http\Responses\LogoutResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponse::class, \App\Http\Responses\LogoutResponse::class);
        $this->app->bind(RateProvider::class, \App\Services\RateProviders\FrankfurterRateProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
