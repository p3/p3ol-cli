<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        config(['logging.channels.single.path' => \Phar::running()
            ? dirname(\Phar::running(false)).'/logs/debug.log'
            : storage_path('logs/laravel.log'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
