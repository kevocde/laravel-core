<?php

namespace Kevocode\LaravelCore\Facades;

use Kevocode\LaravelCore\LaravelCoreServiceProvider;

/**
 * Facade para el módulo LaravelCore
 * 
 * @package Kevocode\LaravelCore\Facades
 * @author kevocode <kevindanielguzmen98@gmail.com>
 * @version 1.0.0
 */
class LaravelCore extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return LaravelCoreServiceProvider::class;
    }
}