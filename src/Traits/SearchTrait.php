<?php

namespace Kevocode\LaravelCore\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Trait para los modelos de la aplicación, este trait proporciona las funciones de búsqueda
 * rápida
 * 
 * @package App\Traits
 * @author Kevin Daniel Guzmán Delgadillo <kevindanielguzmen98@gmail.com>
 * @version 1.0.0
 * @since 1.0.0
 */
trait SearchTrait
{
    /**
     * Determina si se mostrarán los eliminados o no
     *
     * @var boolean
     */
    public $showDeleted = false;

    /**
     * Realiza una búsqueda según los parámetros pasados por la URL
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public static function search(Request $request)
    {
        $instance = new static;
        // Carga de valores para los atributos
        $instance->loadParams($request->query(null, []));
        // Carga de atributos adicionales
        $instance->loadParamsAditional($request->query(null, []));
        // Creación de query
        $query = $instance->getSearchQuery();
        // Aplicación de filtros
        $instance::applyFilterWhere($query, $instance->getConfigFilterWhere());
        // Aplicación de orden
        $instance::applyOrder($query, $request);
        return ['model' => $instance, 'items' => $instance::getPaginator($request, $query->paginate($instance->getPerPage()))];
    }

    /**
     * Carga el modelo con los parámetros según el nombre del formulario
     * 
     * @param array $params Parámetros de la solicitud
     * @param string $formName Nombre del formulario de donde se sacarán los parámetros
     */
    public function loadParams($params, $formName = '')
    {
        $formName = ($formName == '') ? static::getModelName() : $formName;
        $params = !empty($params) ? $params : [];
        $params = ($formName != null && isset($params[$formName])) ? $params[$formName] : $params;
        if (key_exists($this->getDeletedAtColumn(), $params)) {
            $this->showDeleted = ($params[$this->getDeletedAtColumn()] == true);
            unset($params[$this->getDeletedAtColumn()]);
        }
        $this->fill($params);
    }

    /**
     * Carga parámetros adicionales como la página y la cantidad de registros por página que se mostrarán
     * 
     * @param array $params Parámetros de la solicitud
     */
    public function loadParamsAditional($params)
    {
        if (isset($params['per-page']) && !empty($params['per-page'])) {
            $value = ($params['per-page'] == 'all') ? $this->count() : $params['per-page'];
            $this->setPerPage($value );
        }
    }

    /**
     * Retorna la consulta tipo Builder para la búsqueda
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getSearchQuery()
    {

        return $this->showDeleted ? $this::onlyTrashed() : $this::query();
    }

    /**
     * Retorna un arreglo de configuración para la búsqueda rápida
     *
     * @return array
     */
    public function getConfigFilterWhere()
    {
        $configAttributes = [];
        foreach ($this->getAttributes() as $key => $value) {
            if (!empty($value)) {
                $configAttributes[] = [$key, 'like', "%$value%"];
            }
        }
        return $configAttributes;
    }

    /**
     * Permite añadir una condición para la búsqueda
     *
     * @param $query
     * @param array $listConditions
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyFilterWhere(Builder &$query, $listConditions)
    {
        foreach ($listConditions as $key => $value) {
            $operation = '=';
            $valueSearch = $value;
            if (is_array($value)) {
                $key = $value[0];
                $operation = $value[1];
                $valueSearch = $value[2];
            }
            if ((!is_array($value) && !empty($value)) || (is_array($value) && !empty($valueSearch))) {
                if (is_array($value)) {
                    $valueSearch = $valueSearch;
                }
                $query->where($key, $operation, $valueSearch);
            }
        }
        return $query;
    }

    /**
     * Permite añadir un orden según la interpretación de los parámetros de la URL
     *
     * @param $query
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function applyOrder(Builder &$query, Request $request)
    {
        $listOrdersQuery = $request->query('sort', []);
        $listOrdersQuery = (!empty($listOrdersQuery)) ? explode(',', $listOrdersQuery) : [];
        foreach ($listOrdersQuery as $orderQuery) {
            $type = (strpos($orderQuery, '-') !== false) ? 'desc' : 'asc';
            $query->orderBy(str_replace(['+', '-'], '', $orderQuery), $type);
        }
    }

    /**
     * Añade al paginador los links necesarios según los parámetros de la petición
     * 
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * 
     * @return string
     */
    public static function getPaginator(Request $request, $paginator)
    {
        $params = $request->query(null, []);
        unset($params['page']);
        $paginator->appends($params);
        return $paginator;
    }
}