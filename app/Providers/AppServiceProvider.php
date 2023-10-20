<?php

namespace App\Providers;

use App\GoogleForTesting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind('gft', function () {
            return new GoogleForTesting;
        });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
}
