<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrapFive();

        // Fix OpenSSL config on Windows for Web Push
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && env('OPENSSL_CONF')) {
            putenv("OPENSSL_CONF=" . env('OPENSSL_CONF'));
        }
    }
}
