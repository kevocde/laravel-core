<?php

namespace Kevocode\LaravelCore;

use Kevocode\LaravelCore\Facades\LaravelCore;

class LaravelCoreServiceProvider extends \Kevocode\LaravelCore\BaseServiceProvider
{
    protected $name = 'lcore';
    protected $withRoutes = false;
    protected $withMigrations = false;
    protected $withFactories = false;
    protected $facadeClass = LaravelCore::class;

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            \MarvinLabs\Html\Bootstrap\BootstrapServiceProvider::class,
            \Appstract\BladeDirectives\BladeDirectivesServiceProvider::class
        ];
    }
}