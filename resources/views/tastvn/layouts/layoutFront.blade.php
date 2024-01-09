@php
$configData = Helper::appClasses();
$isFront = true;
@endphp

@section('layoutContent')

@extends('tastvn/layouts/commonMaster' )

@include('tastvn/layouts/sections/navbar/navbar-front')

<!-- Sections:Start -->
@yield('content')
<!-- / Sections:End -->

@include('tastvn/layouts/sections/footer/footer-front')
@endsection
