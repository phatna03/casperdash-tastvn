@php
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';
@endphp

@extends('tastevn/layouts/layoutMaster')

@section('title', 'TasteVN Authentication')

@section('vendor-style')
  <!-- Vendor -->
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}"/>
@endsection

@section('page-style')
  <!-- Page -->
  <link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('content')
  <div class="position-relative">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner py-4">

        <!-- Login -->
        <div class="card p-2">
          <!-- Logo -->
          <div class="app-brand justify-content-center mt-5 d-none">
            <a href="{{url('/')}}" class="app-brand-link gap-2">
              <span
                class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
              <span class="app-brand-text demo text-heading fw-bold">{{config('variables.templateName')}}</span>
            </a>
          </div>
          <!-- /Logo -->

          <div class="card-body wrap_form_panel mt-2" id="formLogin">
            <h4 class="mb-2">Welcome to {{config('tastevn.templateName')}}! 👋</h4>
            <p class="mb-4">Please sign-in to your account and start the adventure</p>
            <form class="mb-3" onsubmit="return auth_form_login(event, this);" method="get">
              <div class="form-floating form-floating-outline mb-3">
                <input type="text" class="form-control text-center" id="login_email" name="email" placeholder="Enter your email"
                       autofocus/>
                <label for="login_email">Email</label>
              </div>
              <div class="mb-3">
                <div class="form-password-toggle">
                  <div class="input-group input-group-merge">
                    <div class="form-floating form-floating-outline">
                      <input type="password" id="login_password" class="form-control text-center" name="pwd" autocomplete="current-password"
                             placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                             aria-describedby="login_password"/>
                      <label for="login_password">Password</label>
                    </div>
                    <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                  </div>
                </div>
              </div>
              <div class="mb-3 d-flex justify-content-between ">
                <div class="form-check d-none">
                  <input class="form-check-input" type="checkbox" id="login_remember_me">
                  <label class="form-check-label" for="remember-me">
                    Remember Me
                  </label>
                </div>
                <a class="float-end mb-1"
                   href="javascript:void(0)" onclick="auth_form_active('formForgot')"
                >
                  <span>Forgot Password?</span>
                </a>
              </div>
              <div class="mb-3 wrap-btns">
                @include('tastevn.htmls.button_loading')
                <button class="btn btn-primary btn-ok d-grid w-100" type="submit">Sign in</button>
              </div>
            </form>
          </div>

          <div class="card-body mt-2 wrap_form_panel d-none" id="formForgot">
            <h4 class="mb-2">Forgot Password? 🔒</h4>
            <p class="mb-4">Enter your email and we'll send you code to reset your password</p>
            <form class="mb-3" onsubmit="return auth_form_forgot(event, this);" method="get">
              <div class="form-floating form-floating-outline mb-3">
                <input type="text" class="form-control text-center" id="forgot_email" required name="email" placeholder="Enter your email"/>
                <label for="forgot_email">Email</label>
              </div>
              <div class="form-floating form-floating-outline mb-3 d-none" id="wrap-forgot-code">
                <input type="text" class="form-control text-center" id="forgot_code" name="code" placeholder="Enter your verify code"/>
                <label for="forgot_code">Code</label>
              </div>

              <div class="mb-3 wrap-btns">
                @include('tastevn.htmls.button_loading')
                <button class="btn btn-primary d-grid w-100 btn-ok" type="submit">Send Code</button>
              </div>

              <input type="hidden" name="step" value="email" />
            </form>
            <div class="text-center">
              <a class="d-flex align-items-center justify-content-center"
                 href="javascript:void(0)" onclick="auth_form_active('formLogin')"
              >
                <i class="mdi mdi-chevron-left scaleX-n1-rtl mdi-24px"></i>
                Back to login
              </a>
            </div>
          </div>

          <div class="card-body mt-2 wrap_form_panel d-none" id="formReset">
            <h4 class="mb-2">Reset Password 🔒</h4>
            <p class="mb-4">Enter your new password and remember it. Thank you!</p>
            <form class="mb-3" onsubmit="return auth_form_reset(event, this);" method="get">
              <div class="mb-3 form-password-toggle">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="password" id="reset_pwd1" class="form-control text-center" name="pwd1" required autocomplete="new-password"
                           placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                           aria-describedby="reset_pwd1"/>
                    <label for="reset_pwd1">New Password</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                </div>
              </div>
              <div class="mb-3 form-password-toggle">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="password" id="reset_pwd2" class="form-control text-center" name="pwd2" required autocomplete="new-password"
                           placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                           aria-describedby="reset_pwd2"/>
                    <label for="reset_pwd2">Confirm Password</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                </div>
              </div>

              <div class="mb-3 wrap-btns">
                @include('tastevn.htmls.button_loading')
                <button class="btn btn-primary d-grid w-100 mb-3 btn-ok">Set new password</button>
              </div>

              <div class="text-center">
                <a class="d-flex align-items-center justify-content-center"
                   href="javascript:void(0)" onclick="auth_form_active('formLogin')"
                >
                  <i class="mdi mdi-chevron-left scaleX-n1-rtl mdi-24px"></i>
                  Back to login
                </a>
              </div>
            </form>
          </div>
        </div>
        <!-- /Login -->
        <img alt="mask" src="{{asset('assets/img/illustrations/auth-basic-login-mask-'.$configData['style'].'.png') }}"
             class="authentication-image d-none d-lg-block"
             data-app-light-img="illustrations/auth-basic-login-mask-light.png"
             data-app-dark-img="illustrations/auth-basic-login-mask-dark.png"/>
      </div>
    </div>
  </div>
@endsection
