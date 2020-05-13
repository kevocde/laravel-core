<?php

namespace Kevocode\LaravelCore;

use Illuminate\Support\Facades\Route;

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
     * Ruta base donde se hereda el ServiceProvider base
     *
     * @var string
     */
    protected $baseDir = __DIR__;

    /**
     * Listado de proveedores adicionales a registrar
     *
     * @var array
     */
    protected $additionalProviders = [];

    /**
     * Retorna el nombre del paquete
     *
     * @return string
     */
    protected function getName()
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

        // Configuraciones
        $this->publishes([
            $this->baseDir . '/config/app.php' => config_path($name . '.php')
        ], 'config');

        // Rutas
        if ($this->withRoutes) {
            $this->loadRoutesFrom($this->baseDir . '/routes/web.php');
        }

        // Migraciones
        if ($this->withMigrations) {
            $this->loadMigrationsFrom($this->baseDir . '/database/migrations');
        }

        // Fabricas
        if ($this->withFactories) {
            $this->loadFactoriesFrom($this->baseDir.'/database/factories');
        }

        // Traducciones
        if ($this->withTranslations) {
            $this->loadTranslationsFrom($this->baseDir . '/resources/lang', $name);
            $this->publishes([
                $this->baseDir . '/resources/lang' => resource_path('lang/vendor/' . $name)
            ], 'translations');
        }

        // Vistas
        if ($this->withViews) {
            $this->loadViewsFrom($this->baseDir . '/resources/views', $name);
            $this->publishes([
                $this->baseDir . '/resources/views' => resource_path('views/vendor/' . $name)
            ], 'views');
        }

        // Assets públicos
        if ($this->withPublicAssets) {
            $this->publishes([
                $this->baseDir . '/public' => public_path('vendor/' . $name)
            ], 'public');
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $name = $this->getName();

        // Registro de proveedores de las dependencias
        if (!empty($this->additionalProviders)) {
            foreach ($this->additionalProviders as $providerClass) {
                $this->app->register($providerClass);
            }
        }

        // Mezcla de la configuración
        $this->mergeConfigFrom($this->baseDir . '/config/app.php', $name);

        // Registro de opciones específicas para las vistas generales
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
    public static function defineViewVariables($listVariables = [])
    {
        foreach ($listVariables as $key => $value) {
            view()->share($key, $value);
        }
    }
}