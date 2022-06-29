<?php

namespace App\Providers;

use BackedEnum;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;

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

        Stringable::macro('matchFromPacket', function (BackedEnum $enum, int $number): ?Stringable {
            $regex = '/'.str_replace('*', '(.*)', $enum->value).'/';

            return with(preg_match_all($regex, $this->value, $output), function () use ($output, $number) {
                return $output[$number][0] ? str($output[$number][0]) : null;
            });
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(OutputStyle::class, function ($app, $parameters) {
            return new OutputStyle(...$parameters);
        });
    }
}
