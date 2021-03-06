<?php

namespace RootInc\LaravelHerokuAppCreator;

use Illuminate\Support\ServiceProvider;

class AppCreatorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole())
        {
            $this->publishes([
                __DIR__ . '/../config/app_creator.php' => config_path('app_creator.php'),
            ], 'config');
        }
    }

     /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/app_creator.php', 'app_creator'
        );
    }
}