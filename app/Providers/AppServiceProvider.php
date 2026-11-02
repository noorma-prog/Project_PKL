<?php

namespace App\Providers;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        $trustedProxies = config('trustedproxy.proxies', []);

        if ($trustedProxies !== []) {
            TrustProxies::at($trustedProxies);
            TrustProxies::withHeaders(
                Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
            );
        }

        if (config('app.env') === 'production') {
            URL::useOrigin(config('app.url'));
            URL::forceHttps();
        }
    }
}
