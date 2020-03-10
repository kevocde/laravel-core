<?php

namespace Kevocode\LaravelCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Inflector\Inflector;

/**
 * Controlador base para los CRUDs
 *
 * @package App\Http\Controllers
 *
 * @author Kevin Daniel Guzmán Delgadillo <kevindanielguzmen98@gmail.com>
 * @version 1.0.0
 * @since 0.0.1
 */
class ResourceController extends Controller
{
    /**
     * Modelo al cual hará referencia el controlador tipo recurso
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $modelClass = null;

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
    protected $viewsDir = null;

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

    /**
     * Determina si el controlador será gestinado como un controlador de api rest
     * 
     * @var boolean
     */
    protected $isApi = false;

    /**
     * Sobreescritura del constructor
     */
    public function __construct()
    {
        // @todo Poner esto en la configuración del paquete
        App::setLocale('es');

        if (!$this->isApi) {
            // Registro de carpeta de vistas
            if ($this->viewsDir === null) $this->viewsDir = $this->createViewDir();
            // Registro de breadcrumbs
            if ($this->breadcrumbs !== false && empty($breadcrumbs)) {
                $this->breadcrumbs = [
                    ['label' => __('messages.' . $this->modelClass::getModelName()), 'link' => static::getBaseRouteName() . '.index']
                ];
            } else {
                $this->breadcrumbs = [];
            }
            view()->share('breadcrumbs', array_merge(view()->shared('breadcrumbs'), $this->breadcrumbs));
        }
    }

    /**
     * Crea el nombre del directorio base de las vistas
     *
     * @return string
     */
    protected function createViewDir()
    {
        $className = explode('\\', static::class);
        $className = preg_split('/(?=[A-Z])/', lcfirst(str_replace('Controller', '', end($className))));
        $className = array_map(function ($word) {
            return strtolower($word);
        }, $className);
        return implode('-', $className);
    }

    /**
     * Retorna la ruta para las vistas añadiendo el directorio base del controlador
     *
     * @param string $view Uri de la vista
     * @param boolean $withRoute determina cuando la ruta será generada no para vistas si no para enrutador
     *
     * @return string
     */
    protected function getViewUri($view, $withCommonCore = true)
    {
        $viewUri = '';
        $parts = [$this->viewsDir, $view];
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
     * @return string
     */
    protected function getRoute($action)
    {
        $parts = [$this->viewsDir, $action];
        return implode('.', $parts);
    }

    /**
     * Hace la llamada a renderizar una determinada vista con el prefijo del directorio para el controlador
     *
     * @param string $view Nombre de la vista requerida
     * @param array $data Datos que serán enviados a la vista
     * @param array $mergeData Datos que serán enviados a la vista sobreescribiendo los globales
     * @return string
     */
    protected function view($view, $data = [], $mergeData = [])
    {
        return view(
            $this->getViewUri($view),
            array_merge([
                'routeName' => static::getBaseRouteName(),
                'viewsDir' => $this->getViewUri('', false)
            ], $data),
            $mergeData
        );
    }

    /**
     * Mostrando un listado de los registros de la tabla
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $dataModel = $this->modelClass::search($request);
        return $this->isApi ? $dataModel : $this->view('index', $dataModel);
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
     * Almacena un nuevo registro.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $return = null;
        $validator = $this->modelClass::makeValidator($request);
        if ($validator->fails()) {
            if ($this->isApi) {
                $return = response()->json($validator->errors(), 400);
            } else {
                $return = $this->redirect(
                    redirect()
                        ->route($this->getRoute('create'))
                        ->withErrors($validator)
                        ->withInput()
                );
            }
        } else {
            $modelObject = new $this->modelClass;
            $modelObject->fill($request->post());
            $modelObject->save();
            if ($this->isApi) {
                $return = response()->json($modelObject, 201);
            } else {
                $return = $this->redirect(
                    redirect()->route($this->getRoute('index'))
                );
            }
        }
        return $return;
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
        return $this->isApi ? $modelObject : $this->view('show', ['model' => $modelObject]);
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
     * Actualiza un recurso específico
     *
     * @param \Illuminate\Http\Request $request
     * @param  integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modelObject = $this->modelClass::find($id);
        $return = null;
        $validator = $this->modelClass::makeValidator($request);
        if ($validator->fails()) {
            if ($this->isApi) {
                $return = response()->json($validator->errors(), 400);
            } else {
                $return = $this->redirect(
                    redirect()
                        ->route($this->getRoute('edit'), [Inflector::singularize(static::getBaseRouteName()) => $modelObject->{$modelObject->getKeyName()}])
                        ->withErrors($validator)
                        ->withInput()
                );
            }
        } else {
            $modelObject->fill($request->all());
            $modelObject->save();
            if ($this->isApi) {
                $return = response()->json($modelObject, 200);
            } else {
                $return = $this->redirect(
                    redirect()->route($this->getRoute('index'))
                );
            }
        }
        return $return;
    }

    /**
     * Remueve un recurso específico
     *
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->modelClass::destroy($id);
        $return = response()->json(null, 204);
        if (!$this->isApi) {
            $return = $this->redirect(
                redirect()->route($this->getRoute('index'))
            );
        }
        return $return;
    }

    /**
     * Restaura un recurso en específico
     *
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $modelObject = $this->modelClass::withTrashed()->find($id);
        $modelObject->restore();
        $return = response()->json(null, 204);
        if (!$this->isApi) {
            $return = $this->redirect(
                redirect()->route($this->getRoute('index'))
            );
        }
        return $return;
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
     * @param \Illuminate\Http\Response
     * 
     * @return \Illuminate\Http\Response
     */
    public function redirect($response)
    {
        $redirectResponse = $response;
        $backUrl = request('back-url', null);
        if ($backUrl !== null) {
            $redirectResponse = redirect($backUrl);
        }
        return $redirectResponse;
    }
}