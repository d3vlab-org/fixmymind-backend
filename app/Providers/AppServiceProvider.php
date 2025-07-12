<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Auth::class, function ($app) {
            return (new Factory)
                ->withServiceAccount(config('firebase.credentials'))
                ->createAuth();
        });
    }

    public function boot(): void
    {
        //
    }
}
