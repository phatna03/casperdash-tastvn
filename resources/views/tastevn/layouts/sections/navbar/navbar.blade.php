@php
  $containerNav = $containerNav ?? 'container-fluid';
  $navbarDetached = ($navbarDetached ?? '');
@endphp

  <!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
  <nav
    class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme"
    id="layout-navbar">
    @endif
    @if(isset($navbarDetached) && $navbarDetached == '')
      <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{$containerNav}}">
          @endif

          <!--  Brand demo (display only for navbar-full and hide on below xl) -->
          @if(isset($navbarFull))
            <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
              <a href="{{url('/')}}" class="app-brand-link gap-2">
                <span
                  class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
                <span class="app-brand-text demo menu-text fw-bold">{{config('tastevn.templateName')}}</span>
              </a>
              @if(isset($menuHorizontal))
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                  <i class="mdi mdi-close align-middle"></i>
                </a>
              @endif
            </div>
          @endif

          <!-- ! Not required for layout-without-menu -->
          @if(!isset($navbarHideToggle))
            <div
              class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="mdi mdi-menu mdi-24px"></i>
              </a>
            </div>
          @endif

          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

            @if(!isset($menuHorizontal) && 1<0)
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item navbar-search-wrapper mb-0">
                  <a class="nav-item nav-link search-toggler fw-normal px-0" href="javascript:void(0);">
                    <i class="mdi mdi-magnify mdi-24px scaleX-n1-rtl"></i>
                    <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span>
                  </a>
                </div>
              </div>
              <!-- /Search -->
            @endif

            <ul class="navbar-nav flex-row align-items-center ms-auto">
              @if(isset($menuHorizontal) && 1<0)
                <!-- Search -->
                <li class="nav-item navbar-search-wrapper me-1 me-xl-0">
                  <a class="nav-link search-toggler fw-normal" href="javascript:void(0);">
                    <i class="mdi mdi-magnify mdi-24px scaleX-n1-rtl"></i>
                  </a>
                </li>
                <!-- /Search -->
              @endif

              @if(1<0)
                <!-- Language -->
                <li class="nav-item dropdown-language dropdown me-1 me-xl-0">
                  <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
                     href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class='mdi mdi-translate mdi-24px'></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                         href="{{url('lang/en')}}" data-language="en" data-text-direction="ltr">
                        <span class="align-middle">English</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item {{ app()->getLocale() === 'fr' ? 'active' : '' }}"
                         href="{{url('lang/fr')}}" data-language="fr" data-text-direction="ltr">
                        <span class="align-middle">French</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item {{ app()->getLocale() === 'ar' ? 'active' : '' }}"
                         href="{{url('lang/ar')}}" data-language="ar" data-text-direction="rtl">
                        <span class="align-middle">Arabic</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item {{ app()->getLocale() === 'de' ? 'active' : '' }}"
                         href="{{url('lang/de')}}" data-language="de" data-text-direction="ltr">
                        <span class="align-middle">German</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <!--/ Language -->
              @endif

              @if($configData['hasCustomizer'] == true)
                <!-- Style Switcher -->
                <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                  <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
                     href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class='mdi mdi-24px'></i>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                        <span class="align-middle"><i class='mdi mdi-weather-sunny me-2'></i>Light</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                        <span class="align-middle"><i class="mdi mdi-weather-night me-2"></i>Dark</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                        <span class="align-middle"><i class="mdi mdi-monitor me-2"></i>System</span>
                      </a>
                    </li>
                  </ul>
                </li>
                <!--/ Style Switcher -->
              @endif

              @if(1<0)
                <!-- Quick links  -->
                <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-1 me-xl-0">
                  <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
                     href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                     aria-expanded="false">
                    <i class='mdi mdi-view-grid-plus-outline mdi-24px'></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end py-0">
                    <div class="dropdown-menu-header border-bottom">
                      <div class="dropdown-header d-flex align-items-center py-3">
                        <h5 class="text-body mb-0 me-auto">Shortcuts</h5>
                        <a href="javascript:void(0)" class="dropdown-shortcuts-add text-muted" data-bs-toggle="tooltip"
                           data-bs-placement="top" title="Add shortcuts"><i
                            class="mdi mdi-view-grid-plus-outline mdi-24px"></i></a>
                      </div>
                    </div>
                    <div class="dropdown-shortcuts-list scrollable-container">
                      <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                    <i class="mdi mdi-calendar fs-4"></i>
                  </span>
                          <a href="{{url('app/calendar')}}" class="stretched-link">Calendar</a>
                          <small class="text-muted mb-0">Appointments</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                    <i class="mdi mdi-file-document-outline fs-4"></i>
                  </span>
                          <a href="{{url('app/invoice/list')}}" class="stretched-link">Invoice App</a>
                          <small class="text-muted mb-0">Manage Accounts</small>
                        </div>
                      </div>
                      <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                    <i class="mdi mdi-account-outline fs-4"></i>
                  </span>
                          <a href="{{url('app/user/list')}}" class="stretched-link">User App</a>
                          <small class="text-muted mb-0">Manage Users</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                    <i class="mdi mdi-shield-check-outline fs-4"></i>
                  </span>
                          <a href="{{url('app/access-roles')}}" class="stretched-link">Role Management</a>
                          <small class="text-muted mb-0">Permission</small>
                        </div>
                      </div>
                      <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                    <i class="mdi mdi-chart-pie-outline fs-4"></i>
                  </span>
                          <a href="{{url('/')}}" class="stretched-link">Dashboard</a>
                          <small class="text-muted mb-0">Analytics</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                    <i class="mdi mdi-cog-outline fs-4"></i>
                  </span>
                          <a href="{{url('pages/account-settings-account')}}" class="stretched-link">Setting</a>
                          <small class="text-muted mb-0">Account Settings</small>
                        </div>
                      </div>
                      <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                    <i class="mdi mdi-help-circle-outline fs-4"></i>
                  </span>
                          <a href="{{url('pages/faq')}}" class="stretched-link">FAQs</a>
                          <small class="text-muted mb-0">FAQs & Articles</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                  <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                    <i class="mdi mdi-dock-window fs-4"></i>
                  </span>
                          <a href="{{url('modal-examples')}}" class="stretched-link">Modals</a>
                          <small class="text-muted mb-0">Useful Popups</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
                <!-- Quick links -->
              @endif

              <!-- Speaker -->
              <li class="nav-item acm-mr-px-10" id="btn_speaker">
                <button type="button" class="btn btn-primary p-1" onclick="speaker_allow()"
                        data-bs-toggle="tooltip" data-bs-placement="left" data-bs-custom-class="tooltip-primary" data-bs-original-title="Please click this button if you want to enable Speaker!" aria-describedby="tooltip310439"
                >
                  <i class="mdi mdi-speaker"></i>
                </button>
              </li>

              <!-- Notification -->
              <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-2 me-xl-1"
                  id="navbar-notifications">
                <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
                   href="javascript:void(0);"
                   onclick="notification_navbar()"
                   data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                  <i class="mdi mdi-bell-outline mdi-24px"></i>
                  <span
                    class="position-absolute top-0 start-50 translate-middle-y badge badge-dot bg-danger mt-2 border"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0">
                  <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                      <h6 class="mb-0 me-auto">Notification</h6>
                      <span class="badge rounded-pill bg-label-primary d-none">5 New</span>
                    </div>
                  </li>
                  <li class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush navbar-ul">

                    </ul>
                  </li>
                  <li class="dropdown-menu-footer border-top p-2">
                    <a href="javascript:void(0);" class="btn btn-primary d-flex justify-content-center"
                       onclick="parent.window.location.href='{{url('admin/notifications')}}'">
                      View all notifications
                    </a>
                  </li>
                </ul>
              </li>
              <!--/ Notification -->

              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img
                      src="{{ Auth::user() && !empty(Auth::user()->get_photo()) ? Auth::user()->get_photo() : asset('assets/img/avatars/1.png') }}"
                      alt class="w-px-40 h-auto rounded-circle">
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="{{url('admin')}}">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img
                              src="{{ Auth::user() && !empty(Auth::user()->get_photo()) ? Auth::user()->get_photo() : asset('assets/img/avatars/1.png') }}"
                              alt class="w-px-40 h-auto rounded-circle">
                          </div>
                        </div>
                        <div class="flex-grow-1">
                    <span class="fw-medium d-block">
                      @if (Auth::check())
                        {{ Auth::user()->name }}

                        <input type="hidden" id="acmcfs_user_id" name="user_id" value="{{Auth::user()->id}}"/>
                        <input type="hidden" id="acmcfs_user_role" name="user_role" value="{{Auth::user()->role}}"/>

                      @else
                        Block8910
                      @endif
                    </span>
                          <small class="text-muted text-uppercase">{{Auth::user()->role}}</small>
                        </div>
                      </div>
                    </a>
                  </li>

                  @if(1<0)
                    <li>
                      <a class="dropdown-item"
                         href="{{ Route::has('profile.show') ? route('profile.show') : url('pages/profile-user') }}">
                        <i class="mdi mdi-account-outline me-2"></i>
                        <span class="align-middle">My Profile</span>
                      </a>
                    </li>
                  @endif

                  @if(1<0)
                    <li>
                      <a class="dropdown-item" href="{{url('pages/account-settings-billing')}}">
                        <i class="mdi mdi-credit-card-outline me-2"></i>
                        <span class="align-middle">Billing</span>
                      </a>
                    </li>
                  @endif

                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  @if (Auth::check())
                    <li>
                      <a class="dropdown-item" href="{{url('admin/profile')}}">
                        <i class='mdi mdi-human-edit me-2'></i>
                        <span class="align-middle">My Profile</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{url('admin/profile/setting')}}">
                        <i class='mdi mdi-cogs me-2'></i>
                        <span class="align-middle">Settings</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{url('guide/printer')}}">
                        <i class='mdi mdi-cog-outline me-2'></i>
                        <span class="align-middle">Setup Printer</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{url('guide/speaker')}}">
                        <i class='mdi mdi-cog-outline me-2'></i>
                        <span class="align-middle">Setup Speaker</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{url('custom/file/v1_guide_website_en.pptx')}}">
                        <i class='mdi mdi-information me-2'></i>
                        <span class="align-middle">User Guide</span>
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{url('custom/file/v1_guide_roboflow.pptx')}}">
                        <i class='mdi mdi-information me-2'></i>
                        <span class="align-middle">Roboflow Guide</span>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="javascript:void(0)"
                         data-bs-toggle="modal" data-bs-target="#modal_logout"
                      >
                        <i class='mdi mdi-logout me-2'></i>
                        <span class="align-middle">Logout</span>
                      </a>
                    </li>
                  @else
                    <li>
                      <a class="dropdown-item" href="{{url('login')}}">
                        <i class='mdi mdi-login me-2'></i>
                        <span class="align-middle">Login</span>
                      </a>
                    </li>
                  @endif
                </ul>
              </li>
              <!--/ User -->
            </ul>
          </div>

          <!-- Search Small Screens -->
          <div
            class="navbar-search-wrapper search-input-wrapper {{ isset($menuHorizontal) ? $containerNav : '' }} d-none">
            <input type="text"
                   class="form-control search-input {{ isset($menuHorizontal) ? '' : $containerNav }} border-0"
                   placeholder="Search..." aria-label="Search...">
            <i class="mdi mdi-close search-toggler cursor-pointer"></i>
          </div>
          @if(!isset($navbarDetached))
        </div>
        @endif
      </nav>
      <!-- / Navbar -->
