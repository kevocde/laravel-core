<?php

namespace Kevocde\LaravelCore\Models;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Kevocde\LaravelCore\Traits\SearchTrait;

/**
 * Modelo base de la aplicación
 *
 * @package App
 *
 * @author Kevin Daniel Guzmán Delgadillo <kevindanielguzmen98@gmail.com>
 * @version 1.0.0
 * @since 0.0.1
 *
 * @method static BaseModel find(int $id)
 * @method static Builder orderBy(string $columnName, string $orderType)
 */
class BaseModel extends Model
{
    use SoftDeletes, SearchTrait;

    /**
     * Retorna el nombre del modelo basado en el nombre de la clase
     *
     * @return string
     */
    public static function getModelName()
    {
        $className = explode('\\', static::class);
        return Str::plural(end($className));
    }

    /**
     * Retorna el validador correspondiente al modelo
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public static function makeValidator(Request $request)
    {
        $rules = [
        ];
        $messages = [
        ];
        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Retorna el nombre de la primera que sea descriptivo para el usuario diferente al id o no
     * 
     * @return string
     */
    public static function getDescriptiveColumn()
    {
        $instance = new static;
        $columns = array_filter($instance->fillable, function ($value) use ($instance) {
            return ($value != $instance->primaryKey);
        });
        unset($instance);
        return reset($columns);
    }

    /**
     * Retorna una configuración para las columnas del administrador
     * 
     * @return array
     */
    public function getCrudColumns()
    {
        $configColumns = [];
        foreach ($this->fillable as $column) {
            $configColumns[$column] = [
                'attribute' => $column,
                'label' => trim(ucwords(str_replace(['id', '_'], ['', ' '], $column))),
                'visible' => true,
                // 'value' => function ($item) {
                //     return 'value to show';
                // },
                'type' => 'text' // or 'select'
            ];
        }
        return $configColumns;
    }

    /**
     * Retorna un listado con todos los registros de la base de datos en formato llave => valor
     *
     * @param boolean $withCollection Determina si se devolverá el valor como una colección llave valor
     * o como una colección con cada una de las intancias de la consulta
     * @param array $whereFilters Arreglo con las sentencias para cada consulta de dos formas diferentes:
     * 1. llamada a sentencia sin específicar método:
     * ```
     * [
     *     ['nombre_columna', 'operador', 'valor'],
     *     ...
     * ]
     * ```
     * 2. Llamada a sentencia específicando el método:
     * ```
     * [
     *     'nombre_metodo' => [...parámetros de función]
     * ]
     * ```
     * @param array $listOrders
     * @return Collection
     */
    public static function getData($withCollection = true, $whereFilters = [], $listOrders = [])
    {
        $instance = null;
        $descriptiveColumn = static::getDescriptiveColumn();
        $listOrders = empty($listOrders) ? [[$descriptiveColumn, 'asc']] : $listOrders;
        // Añadiendo filtros
        // Determinando si se está específicando el tipo de método o no
        if (array_keys($whereFilters) === range(0, count($whereFilters)-1)) {
            $newWhereFilters = [];
            foreach ($whereFilters as $filter) {
                $newWhereFilters['where'] = $filter;
            }
            $whereFilters = $newWhereFilters;
        }
        // Aplicando los filtros
        foreach ($whereFilters as $key => $filter) {
            if (empty($instance)) $instance = forward_static_call_array([static::class, $key], $filter);
            else call_user_func([$instance, $key], $filter);
        }
        foreach ($listOrders as $order) {
            if (empty($instance)) $instance = static::orderBy($order[0], $order[1]);
            else $instance->orderBy($order[0], $order[1]);
        }
        return $withCollection ? $instance->get()->pluck($descriptiveColumn, (new static)->getKeyName()) : $instance->get();
    }

    /**
     * Identifica si un recurso es nuevo o esta siendo editado
     * 
     * @return boolean
     */
    public function isNewResource()
    {
        return strlen($this->{$this->getKeyName()}) == 0;
    }
}