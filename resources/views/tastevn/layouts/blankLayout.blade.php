@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();

/* Display elements */
$customizerHidden = ($customizerHidden ?? '');

@endphp

@extends('tastevn/layouts/commonMaster' )

@section('layoutContent')

@include('tastevn.htmls.preloader')
@yield('content')

@endsection
