@php
$configData = Helper::appClasses();
$isFront = true;
@endphp

@section('layoutContent')

@extends('tastevn/layouts/commonMaster' )

@include('tastevn/layouts/sections/navbar/navbar-front')

@include('tastevn.htmls.preloader')
@yield('content')

@include('tastevn/layouts/sections/footer/footer-front')
@endsection
