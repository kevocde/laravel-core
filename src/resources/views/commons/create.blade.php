@php
use Symfony\Component\Inflector\Inflector;

$title = Inflector::singularize($model::getModelName());
$title = is_array($title) ? end($title) : $title;
$title = __('lcore::messages.commons.createtitle', ['title' => mb_strtolower(__('messages.' . $title))]);
$breadcrumbs = array_merge($breadcrumbs, [
    ['label' => $title]
]);
if (!isset($formParams)) {
    $formParams = [];
}

@endphp
@extends(config('lcore.app-layout'))

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @include($viewsDirectory . '_form', array_merge($formParams, ['routeName' => $routeName, 'model' => $model]))
                </div>
            </div>
        </div>
    </div>
@endsection