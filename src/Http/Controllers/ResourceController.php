<?php

namespace Kevocde\LaravelCore\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

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
     * @param Request $request
     * @return Model|Model[]|LengthAwarePaginator|LengthAwarePaginator[]|Paginator|Collection|Collection[]
     */
    public function index(Request $request)
    {
        $dataModel = $this->modelClass::search($request);
        return $this->getFormattedResponse($dataModel['items']);
    }

    /**
     * Muestra los detalles de un determinado recurso
     *
     * @param int $id
     * @return Model|Model[]|LengthAwarePaginator|LengthAwarePaginator[]|Paginator|Collection|Collection[]
     */
    public function show(int $id)
    {
        $modelObject = $this->modelClass::find($id);
        return $this->getFormattedResponse($modelObject);
    }

    /**
     * Realiza el almacenamiento del recurso tanto para edición como para creación de un recurso
     *
     * @param Request $request Solicitud
     * @param int|null $id Entero con el identificador del recurso (si es edición)
     * @return JsonResponse
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
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        return $this->saveResource($request);
    }

    /**
     * Actualiza un recurso específico
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        return $this->saveResource($request, $id);
    }

    /**
     * Cambia el estado del recurso de activo a borrado y biseversa
     *
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    protected function changeStatus(int $id)
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
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(int $id)
    {
        return $this->changeStatus($id);
    }

    /**
     * Restaura un recurso en específico
     *
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function restore(int $id)
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
     * @param Collection|Model|LengthAwarePaginator $data
     * @return Collection|Paginator
     */
    protected function getFormattedResponse($data)
    {
        $isCollectionOrPaginator = (is_a($data, Collection::class)
            || is_a($data, LengthAwarePaginator::class));
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