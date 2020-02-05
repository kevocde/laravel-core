<?php

namespace Kevocode\LaravelCore\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kevocode\LaravelCore\Traits\SearchTrait;
use Symfony\Component\Inflector\Inflector;

/**
 * Modelo base de la aplicación
 *
 * @package App
 *
 * @author Kevin Daniel Guzmán Delgadillo <kevindanielguzmen98@gmail.com>
 * @version 1.0.0
 * @since 0.0.1
 */
class BaseModel extends \Illuminate\Database\Eloquent\Model
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
        return Inflector::pluralize(end($className));
    }

    /**
     * Retorna el validador correspondiente al modelo
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Facades\Validator
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
     * @return array
     */
    public static function getData($withCollection = true, $whereFilters = [], $listOrders = [])
    {
        $instance = new static;
        $listItems = [];
        $instance = null;
        $descriptiveColumn = static::getDescriptiveColumn();
        $listOrders = empty($listOrders) ? [[$descriptiveColumn, 'asc']] : $listOrders;
        foreach ($whereFilters as $filter) {
            if (empty($instance)) $instance = static::where($filter[0], $filter[1], $filter[0]);
            else $instance->where($filter[0], $filter[1], $filter[0]);
        }
        foreach ($listOrders as $order) {
            if (empty($instance)) $instance = static::orderBy($order[0], $order[1]);
            else $instance->orderBy($order[0], $order[1]);
        }
        return $withCollection ? $instance->get()->pluck($descriptiveColumn, (new static)->getKeyName()) : $instance->get();
    }
}