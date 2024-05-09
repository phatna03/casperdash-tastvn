@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
  $configData = Helper::appClasses();
@endphp

@extends('tastevn/layouts/commonMaster' )
@php

  $menuHorizontal = true;
  $navbarFull = true;

  /* Display elements */
  $isNavbar = ($isNavbar ?? true);
  $isMenu = ($isMenu ?? true);
  $isFlex = ($isFlex ?? false);
  $isFooter = ($isFooter ?? true);
  $customizerHidden = ($customizerHidden ?? '');

  /* HTML Classes */
  $menuFixed = (isset($configData['menuFixed']) ? $configData['menuFixed'] : '');
  $navbarType = (isset($configData['navbarType']) ? $configData['navbarType'] : '');
  $footerFixed = (isset($configData['footerFixed']) ? $configData['footerFixed'] : '');
  $menuCollapsed = (isset($configData['menuCollapsed']) ? $configData['menuCollapsed'] : '');

  /* Content classes */
  $container = ($container ?? 'container-xxl');
  $containerNav = ($containerNav ?? 'container-xxl');

@endphp

@section('layoutContent')

  @include('tastevn.htmls.preloader')

  <div class="layout-wrapper layout-navbar-full layout-horizontal layout-without-menu">
    <div class="layout-container">

      <!-- BEGIN: Navbar-->
      @if ($isNavbar)
        @include('tastevn/layouts/sections/navbar/navbar')
      @endif
      <!-- END: Navbar-->


      <!-- Layout page -->
      <div class="layout-page">

        {{-- Below commented code read by artisan command while installing jetstream. !! Do not remove if you want to use jetstream. --}}
        {{-- <x-banner /> --}}

        <!-- Content wrapper -->
        <div class="content-wrapper">

          @if ($isMenu)
            @include('tastevn/layouts/sections/menu/horizontalMenu')
          @endif

          <!-- Content -->
          @if ($isFlex)
            <div class="{{$container}} d-flex align-items-stretch flex-grow-1 p-0 page_main_content">
              @else
                <div class="{{$container}} flex-grow-1 container-p-y page_main_content">
                  @endif

                  @yield('content')

                </div>
                <!-- / Content -->

                <!-- Footer -->
                @if ($isFooter)
                  @include('tastevn/layouts/sections/footer/footer')
                @endif
                <!-- / Footer -->
                <div class="content-backdrop fade"></div>
            </div>
            <!--/ Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>
      <!-- / Layout Container -->

      @if ($isMenu)
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
      @endif
      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
@endsection
