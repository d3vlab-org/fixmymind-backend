<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth as FirebaseAuth;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FirebaseAuth::class, function ($app) {
            // Check if Firebase credentials are configured
            $credentialsPath = env('FIREBASE_CREDENTIALS');
            
            if (!$credentialsPath || !file_exists($credentialsPath)) {
                // Return a mock or throw an exception
                throw new \Exception('Firebase credentials not found. Please configure FIREBASE_CREDENTIALS environment variable.');
            }
            
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            return $factory->createAuth();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
