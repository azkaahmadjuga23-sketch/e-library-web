<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use App\Services\OpenLibraryService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->singleton(OpenLibraryService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
