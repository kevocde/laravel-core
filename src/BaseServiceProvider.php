<?php

namespace Kevocode\LaravelCore;

/**
 * ServiceProvider base para los paquetes que hereden de este
 *
 * @package Kevocode\LaravelCore
 * @author kevocode <kevindanielguzmen98@gmail.com>
 * @version 1.0.0
 */
class BaseServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Nombre del paquete
     *
     * @var string
     */
    protected $name = null;

    /**
     * Utilizará rutas
     *
     * @var boolean
     */
    protected $withRoutes = true;

    /**
     * Utilizará migraciones
     *
     * @var boolean
     */
    protected $withMigrations = true;

    /**
     * Utilizará sembradores de la base de datos
     *
     * @var boolean
     */
    protected $withFactories = true;

    /**
     * Utilizará traducciones
     *
     * @var boolean
     */
    protected $withTranslations = true;

    /**
     * Utilizará vistas
     *
     * @var boolean
     */
    protected $withViews = true;

    /**
     * Utilizará recursos públicos
     * 
     * @var boolean
     */
    protected $withPublicAssets = true;

    /**
     * Retorna el nombre del paquete
     *
     * @return string
     */
    public function getName()
    {
        if ($this->name === null) {
            $this->name = time() . rand(11, 99);
        }
        return mb_strtolower($this->name);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $name = $this->getName();

        // Plublicando configuraciones y recursos
        $publishes = [
            __DIR__ . '/config/app.php' => config_path($name . '.php')
        ];
        if ($this->withTranslations) $publishes[__DIR__ . '/resources/lang'] = resource_path('views/vendor/' . $name);
        if ($this->withViews) $publishes[__DIR__ . '/resources/views'] = resource_path('lang/' . $name);
        if ($this->withPublicAssets) $publishes[__DIR__ . '/public'] = public_path('vendor/' . $name);
        $this->publishes($publishes, $name);

        // Cargando rutas
        if ($this->withRoutes) $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        // Cargando migraciones
        if ($this->withMigrations) $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        // Cargando sembradores de la base de datos
        if ($this->withFactories) $this->loadFactoriesFrom(__DIR__.'/database/factories');
        // Cargando lenguaje
        if ($this->withTranslations) $this->loadTranslationsFrom(__DIR__ . '/resources/lang', $name);
        // Cargando vistas
        if ($this->withViews) $this->loadViewsFrom(__DIR__ . '/resources/views', $name);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $name = $this->getName();

        // Cargando la configuración
        $this->mergeConfigFrom(__DIR__ . '/config/app.php', $name);
        // Opciones específicas para las vistas
        if ($this->withViews) {
            static::defineViewVariables([
                'breadcrumbs' => ['label' => 'Inicio', 'link' => 'home'],
                'title' => config($name . '.name', $name)
            ]);
        }
    }

    /**
     * Registrando variables generales para las vistas
     *
     * @param array $listVariables Listado de variables en formato llave valor
     */
    public static function defineViewVariables($listVariables)
    {
        foreach ($listVariables as $key => $value) {
            view()->share($key, $value);
        }
    }
}