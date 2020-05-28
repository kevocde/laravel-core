<?php

namespace Kevocode\LaravelCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Inflector\Inflector;

/**
 * Controlador base para los CRUDs
 *
 * @package Kevocode\LaravelCore\Http\Controllers
 *
 * @author Kevin Daniel Guzmán Delgadillo <kevindanielguzmen98@gmail.com>
 * @version 1.0.0
 * @since 0.0.1
 */
class CrudController extends BaseController
{
    /**
     * Breadcrumbs base del controlador
     *
     * @return array|boolean
     */
    protected $breadcrumbs = [];

    /**
     * Nombre del paquete específicos de las vistas en caso de ser necesario.
     * Si estan directamente en la aplicación no es necesario definirlo, si está en un paquete
     * con vistas específicas es necesario indicarle el nombre del paquete de vistas.
     *
     * @var string
     */
    protected $viewsPackage = 'lcore';

    /**
     * Nombre del directorio que contendrá las vistas para el controlador
     *
     * @var string
     */
    protected $viewsDirectory = null;

    /**
     * Nombre de la llave que contendrá los mensajes de alertas en la sesión
     * 
     * @var string
     */
    protected $keySessionMessage = 'alert';

    /**
     * Sobreescritura del constructor
     */
    public function __construct()
    {
        // Definición de configuraciones comunes según el paquete o la aplicación
        $this->defineCommonSettings();
        // Inicializando carpeta de vistas
        $this->defineViewsDirectory();
        // Definición de variables para las vistas o variables que irán a las vistas
        $this->defineVariablesViews();
    }

    /**
     * Define el nombre del directorio que contendrá las vistas para el CRUD
     */
    protected function defineViewsDirectory()
    {
        if ($this->viewsDirectory === null) {
            $className = explode('\\', static::class);
            $className = preg_split('/(?=[A-Z])/', lcfirst(str_replace('Controller', '', end($className))));
            $className = array_map(function ($word) {
                return strtolower($word);
            }, $className);
            $this->viewsDirectory = implode('-', $className);
        }
    }

    /**
     * Define un grupo de variables que serán las que se mostrarán en las vistas
     */
    protected function defineVariablesViews()
    {
        // Miga de pan
        if ($this->breadcrumbs !== false && empty($this->breadcrumbs)) {
            $this->breadcrumbs = [
                ['label' => __('messages.' . $this->modelClass::getModelName()), 'link' => static::getBaseRouteName() . '.index']
            ];
        } elseif ($this->breadcrumbs === false) {
            $this->breadcrumbs = [];
        }
        view()->share('breadcrumbs', array_merge(view()->shared('breadcrumbs'), $this->breadcrumbs));
    }

    /**
     * Retorna la ruta para las vistas añadiendo el directorio base del controlador
     *
     * @param string $view Uri de la vista
     * @param boolean $withCommonCore determina si se aplicará las vistas comunes o serán directamente aplicadas de la misma carpeta contenedora
     *
     * @return string
     */
    protected function getViewUri($view, $withCommonCore = true)
    {
        $viewUri = '';
        $parts = [$this->viewsDirectory, $view];
        if ($this->packageName !== null) {
            $viewUri .= $this->packageName . '::';
        }
        $viewExist = view()->exists($viewUri . implode('.', $parts));
        if (!$withCommonCore || $viewExist) {
            $viewUri = $viewUri . implode('.', $parts);
        } else {
            $viewUri = $this->corePackageName . '::commons.' . end($parts);
        }
        return $viewUri;
    }

    /**
     * Retorna las rutas según con el nombre del recurso
     * 
     * @param string $action Nombre de la acción que se está solicitando
     * 
     * @return string
     */
    protected function getRoute($action)
    {
        $parts = [$this->viewsDirectory, $action];
        return implode('.', $parts);
    }

    /**
     * Hace la llamada a renderizar una determinada vista con el prefijo del directorio para el controlador
     *
     * @param string $view Nombre de la vista requerida
     * @param array $data Datos que serán enviados a la vista
     * @param array $mergeData Datos que serán enviados a la vista sobreescribiendo los globales
     *
     * @return string
     */
    protected function view($view, $data = [], $mergeData = [])
    {
        return view(
            $this->getViewUri($view),
            array_merge([
                'routeName' => static::getBaseRouteName(),
                'viewsDirectory' => $this->getViewUri('', false)
            ], $data),
            $mergeData
        );
    }

    /**
     * Mostrando un listado de los registros de la tabla
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $dataModel = $this->modelClass::search($request);
        return $this->view('index', $dataModel);
    }

    /**
     * Muestra los detalles de un determinado recurso
     *
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelObject = $this->modelClass::find($id);
        return $this->view('show', ['model' => $modelObject]);
    }

    /**
     * Muestra el formulario para la creación de un registro
     * 
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $modelObject = new $this->modelClass;
        return $this->view('create', [
            'model' => $modelObject
        ]);
    }

    /**
     * Muestra el formulario para editar un determinado recurso
     *
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $modelObject = $this->modelClass::find($id);
        return $this->view('edit', [
            'model' => $modelObject
        ]);
    }

    /**
     * Realiza el almacenamiento del recurso tanto para edición como para creación de un recurso
     * 
     * @param \Illuminate\Http\Request $request Solicitud
     * @param integer $id Entero con el identificador del recurso (si es edición)
     * 
     * @return \Illuminate\Http\Response
     */
    protected function saveResource(Request $request, int $id = null)
    {
        $response = null;
        $modelObject = ($id === null) ? new $this->modelClass : $this->modelClass::find($id);
        $redirectRoute = 'create';
        $redirectParams = [];
        $message = [
            'title' => __('lcore::messages.commons.createalerttitle'),
            'content' => __('lcore::messages.commons.createalertcontent')
        ];
        $typeMessage = 'success';
        $isNewResource = $modelObject->isNewResource();
        // Determinamos si no es un nuevo registro
        if (!$isNewResource) {
            $redirectRoute = 'edit';
            $keyParam = Inflector::singularize(static::getBaseRouteName());
            $redirectParams = [(is_array($keyParam) ? end($keyParam) : $keyParam) => $modelObject->{$modelObject->getKeyName()}];
            $message = [
                'title' => __('lcore::messages.commons.updatealerttitle'),
                'content' => __('lcore::messages.commons.updatealertcontent')
            ];
        }
        // Realizando la validación
        $validator = $this->modelClass::makeValidator($request);
        if ($validator->fails()) {
            $message = [
                'title' => __('lcore::messages.commons.errorstoretitle'),
                'content' => __('lcore::messages.commons.errorstorecontent')
            ];
            $typeMessage = 'danger';
            $response = $this->redirect(
                redirect()
                    ->route($this->getRoute($redirectRoute), $redirectParams)
                    ->withErrors($validator)
                    ->withInput()
            );
        } else {
            $paramsToFill = $isNewResource ? $request->post() : $request->all();
            $modelObject->fill($paramsToFill);
            $modelObject->save();
            $response = $this->redirect(
                redirect()->route($this->getRoute('index'))
            );
        }
        $this->setFlashAlert($message, $typeMessage);
        return $response;
    }

    /**
     * Almacena un nuevo registro.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->saveResource($request);
    }

    /**
     * Actualiza un recurso específico
     *
     * @param \Illuminate\Http\Request $request
     * @param  integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return $this->saveResource($request, $id);
    }

    /**
     * Añade a la solicitud en mensaje de alerta a la sesión
     * 
     * @param string $type Tipo de alerta
     * @param array $message Arreglo con los datos del mensaje
     */
    protected function setFlashAlert($message, $type = 'success') {
        request()->session()->flash($this->keySessionMessage, [
            'type' => $type,
            'payload' => $message
        ]);
    }

    /**
     * Cambia el estado del recurso de activo a borrado y biseversa
     * 
     * @param integer $id
     * 
     * @return \Illuminate\Http\Response
     */
    protected function changeStatus($id)
    {
        $modelObject = $this->modelClass::withTrashed()->find($id);
        if ($modelObject->trashed()) {
            $modelObject->restore();
            $message = [
                'title' => __('lcore::messages.commons.restorealerttitle'),
                'content' => __('lcore::messages.commons.restorealertcontent')
            ];
        } else {
            $modelObject->delete();
            $message = [
                'title' => __('lcore::messages.commons.deletealerttitle'),
                'content' => __('lcore::messages.commons.deletealertcontent')
            ];
        }
        $response = $this->redirect(
            redirect()->route($this->getRoute('index'))
        );
        $this->setFlashAlert($message);
        return $response;
    }

    /**
     * Remueve un recurso específico
     *
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->changeStatus($id);
    }

    /**
     * Restaura un recurso en específico
     *
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        return $this->changeStatus($id);
    }

    /**
     * Retorna el nombre base de las rutas
     *
     * @return string
     */
    public static function getBaseRouteName()
    {
        $route = explode('.', Route::currentRouteName());
        return reset($route);
    }

    /**
     * Realiza la redirección respectiva tomando en cuenta el parámetros back-url en caso de ser necesario
     * 
     * @param \Illuminate\Http\Response $response
     * 
     * @return \Illuminate\Http\Response
     */
    protected function redirect($response)
    {
        $redirectResponse = $response;
        $backUrl = request('back-url', null);
        if ($backUrl !== null) {
            $redirectResponse = redirect($backUrl);
        }
        return $redirectResponse;
    }
}