<?php

namespace Kevocode\LaravelCore;

use Kevocode\LaravelCore\Facades\LaravelCore;

class LaravelCoreServiceProvider extends \Kevocode\LaravelCore\BaseServiceProvider
{
    protected $name = 'lcore';
    protected $withRoutes = false;
    protected $withMigrations = false;
    protected $withFactories = false;
    protected $baseDir = __DIR__;
    protected $addicionalProvider = [
        \MarvinLabs\Html\Bootstrap\BootstrapServiceProvider::class,
        \Appstract\BladeDirectives\BladeDirectivesServiceProvider::class
    ];
}