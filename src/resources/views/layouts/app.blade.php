<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="origin-when-cross-origin">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('lcore.name', 'PDFDocTemplates') }}</title>

    <!-- Styles -->
    <link href="{{ asset('vendor/lcore/css/app.css') }}" rel="stylesheet">
    @section('styles')
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('vendor/lcore/plugins/fontawesome5.12.0-web/css/all.min.css') }}">
    @show
</head>
<body>
    <div>
        <div class="row mb-3 justify-content-between">
            <div class="col-auto d-flex align-items-center">@include('lcore::layouts._breadcrumbs')</div>
        </div>
        @yield('content')
    </div>

    @section('outcontent')
    @show
    
    <!-- Scripts -->
    <script src="{{ asset('vendor/lcore/js/app.js') }}" defer></script>
    <script src="{{ asset('vendor/lcore/js/utils.js') }}" defer></script>
    @section('scripts')
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/vue@2.5.16/dist/vue.js"></script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/localization/messages_es.min.js"></script>
        <script src="https://cdn.tiny.cloud/1/x1tkvltdsszb59qpb22nz9sia1qfrr4ehouvovgm4g8f0loh/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    @show
</body>
</html>
