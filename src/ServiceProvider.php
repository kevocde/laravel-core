<?php

namespace Kevocode\LaravelCore;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Loading package config
        $this->publishes([
            __DIR__ . '/config/app.php' => config_path('lcore.php')
        ]);

        // Publicando los assets
        $this->publishes([
            __DIR__ . '/public' => public_path('vendor/lcore')
        ], 'public');

        // Loading routes config
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Loading migrations dir
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Loading factories dir
        // $this->loadFactoriesFrom(__DIR__.'/database/factories');

        // Loading translations
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'lcore');

        // Loading views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'lcore');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Loading and merge package configs
        $this->mergeConfigFrom(
            __DIR__ . '/config/app.php',
            'lcore'
        );

        // Loading others service providers
        $this->app->register(\MarvinLabs\Html\Bootstrap\BootstrapServiceProvider::class);
        $this->app->register(\Appstract\BladeDirectives\BladeDirectivesServiceProvider::class);

        // Opciones específicas para las vistas
        view()->share('breadcrumbs', [
            ['label' => 'Inicio', 'link' => 'home']
        ]);
        view()->share('title', config('app.name'));
    }
}