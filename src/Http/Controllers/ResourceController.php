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
class ResourceController extends BaseController
{
    /**
     * Define el ResourceCollection que será usuado para formatear las colecciones de datos,
     * en caso de no definirse se procederá a hacerse la devuelta de la información tal y como el modelo lo define.
     * 
     * @var string
     */
    protected $resourceCollectionClass = null;

    /**
     * Define la clase tipo JsonResource que se usará para formatear los atributos devueltos para un modelo,
     * En el caso la acción index para listar todos los registros no se tendrá en cuenta esta propieda, se tendrá en cuenta
     * solamente la pripiedad $resourceCollectionClass si esta existe
     * 
     * @var string
     */
    protected $collectClass = null;

    /**
     * Sobreescritura del constructor
     */
    public function __construct()
    {
        // Definición de configuraciones comunes según el paquete o la aplicación
        $this->defineCommonSettings();
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
        return $this->getFormattedResponse($dataModel['items']);
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
        return $this->getFormattedResponse($modelObject);
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
        $isNewResource = $modelObject->isNewResource();
        // Realizando la validación
        $validator = $this->modelClass::makeValidator($request);
        if ($validator->fails()) {
            $response = response()->json($validator->errors(), 400);
        } else {
            $paramsToFill = $isNewResource ? $request->post() : $request->all();
            $modelObject->fill($paramsToFill);
            $modelObject->save();
            $response = response()->json($this->getFormattedResponse($modelObject), 201);
        }
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
        } else {
            $modelObject->delete();
        }
        return response()->json(null, 204);
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
     * Retorna la respuesta aplicando (o no) las clases formateadoras definidas en las propiedades
     * $collectClass y $resourceCollectionClass
     * 
     * @param \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Pagination\LengthAwarePaginator $data
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\Paginator
     */
    protected function getFormattedResponse($data)
    {
        $isCollectionOrPaginator = (is_a($data, \Illuminate\Support\Collection::class)
            || is_a($data, \Illuminate\Pagination\LengthAwarePaginator::class));
        if (!is_null($this->resourceCollectionClass) && $isCollectionOrPaginator) {
            $data = new $this->resourceCollectionClass($data);
        } elseif (!$isCollectionOrPaginator && !is_null($this->collectClass)) {
            $data = new $this->collectClass($data);
        } elseif (!$isCollectionOrPaginator) {
            // Esto se hace con el fín de que la estructura de la respuesta siempre sea compatible se utilice o no
            $data = ['data' => $data];
        }
        return $data;
    }
}