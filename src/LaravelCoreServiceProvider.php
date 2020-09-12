<?php

namespace Kevocde\LaravelCore;


use Appstract\BladeDirectives\BladeDirectivesServiceProvider;
use MarvinLabs\Html\Bootstrap\BootstrapServiceProvider;

class LaravelCoreServiceProvider extends BaseServiceProvider
{
    protected $name = 'lcore';
    protected $withRoutes = false;
    protected $withMigrations = false;
    protected $withFactories = false;
    protected $baseDir = __DIR__;
    protected $addicionalProvider = [
        BootstrapServiceProvider::class,
        BladeDirectivesServiceProvider::class
    ];
}