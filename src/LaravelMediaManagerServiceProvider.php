<?php

namespace Sinevia\LaravelMediaManager;

use Illuminate\Support\ServiceProvider;

class LaravelMediaManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/routes.php';
        $this->loadViewsFrom(__DIR__.'/views', 'media-manager');

        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/vendor/media-manager'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Sinevia\LaravelMediaManager\Controllers\MediaController');
    }
}
