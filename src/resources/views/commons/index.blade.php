@php
use Symfony\Component\Inflector\Inflector;

$regularTitle = Inflector::singularize($model::getModelName());
$regularTitle = is_array($regularTitle) ? end($regularTitle) : $regularTitle;
$title = __('lcore::messages.' . $model::getModelName());
$nameParam = Inflector::singularize($routeName);
$nameParam = str_replace('-', '_', is_array($nameParam) ? end($nameParam) : $nameParam);

$breadcrumbs = array_merge($breadcrumbs, [
    ['label' => 'Principal']
]);

@endphp
@extends('lcore::layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <div id="{{ $routeName }}-gridview" class="w-100">
                <div class="row">
                    <div class="col-12 mb-2 d-flex justify-content-end">
                        <a href="{{ route($routeName . '.create') }}" class="btn btn-success mr-1">{{ __('lcore::messages.commons.createtitle', ['title' => mb_strtolower(__('lcore::messages.' . $regularTitle))]) }}</a>
                        @if($model->showDeleted)
                            <a href="{{ route($routeName . '.index', [$model::getModelName() . '[' . $model->getDeletedAtColumn() . ']' => false]) }}" class="btn btn-outline-secondary mr-1">
                                {{ __('lcore::messages.commons.hidedeleted') }}
                            </a>
                        @else
                            <a href="{{ route($routeName . '.index', [$model::getModelName() . '[' . $model->getDeletedAtColumn() . ']' => true]) }}" class="btn btn-outline-danger mr-1">
                                {{ __('lcore::messages.commons.showdeleted') }}
                            </a>
                        @endif
                        <a href="{{ route($routeName . '.index') }}" class="btn btn-outline-info mr-1">{{ __('lcore::messages.commons.refresh') }}</a>
                    </div>
                    <div class="col-12">
                        <form id="{{ $routeName }}-fastsearch-form" class="d-none" action="{{ route($routeName . '.index') }}" method="GET"></form>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <div class="d-flex justify-content-center align-items-center"><p class="text-center">#</p></div>
                                        </th>
                                        @foreach ($model->getCrudColumns() as $column)
                                            @if (!isset($column['visible']) || $column['visible'])
                                                <th scope="col">
                                                    <div class="row">
                                                        <div class="col-12 mb-2">{{ $column['label'] }}</div>
                                                        <div class="col-12">
                                                            @if (!isset($column['type']) || $column['type'] == 'text')
                                                                <input type="text" class="form-control" name="{{ $model::getModelName() }}[{{ $column['attribute'] }}]" value="{{ $model->{$column['attribute']} }}" form="{{ $routeName }}-fastsearch-form">
                                                            @elseif (isset($column['type']) && $column['type'] == 'select')
                                                                @php
                                                                    $column['data']->prepend('Seleccione ...', '');
                                                                @endphp
                                                                {{
                                                                    html()
                                                                        ->select($model::getModelName() .'['. $column['attribute'] .']', $column['data'], $model->{$column['attribute']})
                                                                        ->attributes(array_merge([
                                                                            'id' => (isset($column['id']) ? $column['id'] : null),
                                                                            // 'class' => 'd-none',
                                                                            'form' => $routeName . '-fastsearch-form'
                                                                        ], (isset($column['inputOptions']) ? $column['inputOptions'] : [])))
                                                                }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </th>
                                            @endif
                                        @endforeach
                                        <th scope="col">
                                            <div class="d-flex justify-content-center align-items-center"><p class="text-center">{{ __('lcore::messages.commons.actions')}}</p></div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                        <tr>
                                            <td class="text-center">{{ ($loop->index+1) }}</td>
                                            @foreach ($model->getCrudColumns() as $column)
                                                <td>
                                                    @if (!isset($column['visible']) || $column['visible'])
                                                        @php
                                                            if (isset($column['value'])) {
                                                                echo call_user_func_array($column['value'], [$item]);
                                                            } else {
                                                                echo $item->{$column['attribute']};
                                                            }
                                                        @endphp
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-center">
                                                <a href="{{ route($routeName . '.edit', [$nameParam => $item->{$item->getKeyName()}]) }}" class="btn btn-primary btn-sm d-inline-block mb-1" title="{{ __('lcore::messages.commons.edit') }}"><i class="mdi mdi-lead-pencil"></i> {{ __('lcore::messages.commons.edit') }}</a>
                                                @php
                                                    $randomKey = rand(0, 9) . time();
                                                @endphp
                                                @if(!$item->trashed())
                                                    <form id="{{ $routeName }}-delete-form-{{ $randomKey }}" class="d-none" action="{{ route($routeName . '.destroy', [$nameParam => $item->{$item->getKeyName()}]) }}" method="POST">
                                                        @method('DELETE')
                                                        @csrf
                                                    </form>
                                                    <button class="btn btn-danger btn-sm mb-1" form="{{ $routeName }}-delete-form-{{ $randomKey }}" title="{{ __('lcore::messages.commons.remove') }}"><i class="mdi mdi-delete"></i> {{ __('lcore::messages.commons.remove') }}</button>
                                                @else
                                                    <form id="{{ $routeName }}-restore-form-{{ $randomKey }}" class="d-none" action="{{ route($routeName . '.restore', [$nameParam => $item->{$item->getKeyName()}]) }}" method="POST">
                                                        @method('PUT')
                                                        @csrf
                                                    </form>
                                                    <button class="btn btn-secondary btn-sm mb-1" form="{{ $routeName }}-restore-form-{{ $randomKey }}" title="{{ __('lcore::messages.commons.restore') }}"><i class="mdi mdi-reload"></i> {{ __('lcore::messages.commons.restore') }}</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="12" class="text-center">{{ __('lcore::messages.commons.notingtoshow') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-center mt-2">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            loadSelect2SelectsOfForm('{{ $routeName }}-fastsearch-form')
            addEventsToFashForm('{{ $routeName }}-gridview', '{{ $routeName }}-fastsearch-form')
        })
    </script>
@endsection