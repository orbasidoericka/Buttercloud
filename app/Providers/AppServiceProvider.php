<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production (Railway, etc.)
        if (config('app.env') === 'production' || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // Enforce secure session cookies when appropriate
        $appUrl = env('APP_URL') ?: config('app.url');
        if ($appUrl && str_starts_with($appUrl, 'https')) {
            config(['session.secure' => true]);
        }

        // Log a warning if APP_KEY is missing to help debug session/cookie failures
        if (empty(config('app.key'))) {
            logger()->warning('APP_KEY is not set. Session encryption and cookie signing may fail. Set APP_KEY in your environment variables.');
        }
    }
}
