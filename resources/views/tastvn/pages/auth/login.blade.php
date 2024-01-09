@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('tastvn/layouts/layoutMaster')

@section('title', 'TastVN Authentication')

@section('vendor-style')
<!-- Vendor -->
<link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />
@endsection

@section('page-style')
<!-- Page -->
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/@form-validation/umd/bundle/popular.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js')}}"></script>
<script src="{{asset('assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js')}}"></script>
@endsection

@section('page-script')
{{--<script src="{{asset('assets/js/pages-auth.js')}}"></script>--}}
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
            <span class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
            <span class="app-brand-text demo text-heading fw-bold">{{config('variables.templateName')}}</span>
          </a>
        </div>
        <!-- /Logo -->

        <div class="card-body mt-2">
          <h4 class="mb-2">Welcome to {{config('tastvn.templateName')}}! 👋</h4>
          <p class="mb-4">Please sign-in to your account and start the adventure</p>

          <form id="formLogin" class="mb-3" action="{{url('/login')}}" method="post">
            @csrf
            <div class="form-floating form-floating-outline mb-3">
              <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email" autofocus>
              <label for="email">Email</label>
            </div>
            <div class="mb-3">
              <div class="form-password-toggle">
                <div class="input-group input-group-merge">
                  <div class="form-floating form-floating-outline">
                    <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                    <label for="password">Password</label>
                  </div>
                  <span class="input-group-text cursor-pointer"><i class="mdi mdi-eye-off-outline"></i></span>
                </div>
              </div>
            </div>
            <div class="mb-3 d-flex justify-content-between ">
              <div class="form-check d-none">
                <input class="form-check-input" type="checkbox" id="remember-me">
                <label class="form-check-label" for="remember-me">
                  Remember Me
                </label>
              </div>
              <a href="{{url('forgot/email')}}" class="float-end mb-1">
                <span>Forgot Password?</span>
              </a>
            </div>
            <div class="mb-3">
              <button class="btn btn-primary d-grid w-100"
{{--                      type="submit"--}}
                      type="button" onclick="parent.window.location.href='{{url('admin')}}'"
              >Sign in</button>
            </div>
          </form>

          <p class="text-center d-none">
            <span>New on our platform?</span>
            <a href="{{url('auth/register-basic')}}">
              <span>Create an account</span>
            </a>
          </p>

          <div class="divider my-4 d-none">
            <div class="divider-text">or</div>
          </div>

          <div class="d-flex justify-content-center gap-2 d-none">
            <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-facebook">
              <i class="tf-icons mdi mdi-24px mdi-facebook"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-twitter">
              <i class="tf-icons mdi mdi-24px mdi-twitter"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-github">
              <i class="tf-icons mdi mdi-24px mdi-github"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon btn-lg rounded-pill btn-text-google-plus">
              <i class="tf-icons mdi mdi-24px mdi-google"></i>
            </a>
          </div>
        </div>
      </div>
      <!-- /Login -->
      <img alt="mask" src="{{asset('assets/img/illustrations/auth-basic-login-mask-'.$configData['style'].'.png') }}" class="authentication-image d-none d-lg-block" data-app-light-img="illustrations/auth-basic-login-mask-light.png" data-app-dark-img="illustrations/auth-basic-login-mask-dark.png" />
    </div>
  </div>
</div>
@endsection

@section('js_end')
  <script type="text/javascript">
    const formLogin = document.querySelector('#formLogin');
    document.addEventListener('DOMContentLoaded', function (e) {
      (function () {
        // Form validation for Add new record
        if (formLogin) {
          const fv = FormValidation.formValidation(formLogin, {
            fields: {
              email: {
                validators: {
                  notEmpty: {
                    message: 'Please enter your email'
                  },
                  emailAddress: {
                    message: 'Please enter valid email address'
                  }
                }
              },
              password: {
                validators: {
                  notEmpty: {
                    message: 'Please enter your password'
                  },
                  stringLength: {
                    min: 6,
                    message: 'Password must be more than 6 characters'
                  }
                }
              }
            },
            plugins: {
              trigger: new FormValidation.plugins.Trigger(),
              bootstrap5: new FormValidation.plugins.Bootstrap5({
                eleValidClass: '',
                rowSelector: '.mb-3'
              }),
              submitButton: new FormValidation.plugins.SubmitButton(),

              defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
              autoFocus: new FormValidation.plugins.AutoFocus()
            },
            init: instance => {
              instance.on('plugins.message.placed', function (e) {
                if (e.element.parentElement.classList.contains('input-group')) {
                  e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                }
              });
            }
          });
        }
      })();
    });


    jQuery(document).ready(function () {

    });
  </script>
@endsection
