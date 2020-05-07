<?php

namespace Kevocode\LaravelCore\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BaseController extends \Illuminate\Routing\Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Modelo al cual hará referencia el controlador tipo recurso
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $modelClass = null;

    /**
     * Nombre del paquete core
     *
     * @var string
     */
    protected $corePackageName = 'lcore';

    /**
     * Nombre del paquete donde está el proyecto, si es nulo significa que es la aplicación directamente
     *
     * @var string
     */
    protected $packageName = null;
}
