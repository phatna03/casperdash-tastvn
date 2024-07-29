@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Profile Information')

@section('vendor-style')
  {{--  <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />--}}
  {{--  <link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />--}}
@endsection

@section('vendor-script')
  {{--  <script src="{{asset('assets/vendor/libs/cleavejs/cleave.js')}}"></script>--}}
  {{--  <script src="{{asset('assets/vendor/libs/cleavejs/cleave-phone.js')}}"></script>--}}
  {{--  <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>--}}
  {{--  <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>--}}
  {{--  <script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>--}}
@endsection

@section('page-script')
  {{--  <script src="{{asset('assets/js/form-layouts.js')}}"></script>--}}
@endsection

@section('content')
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Profile Information</h4>

  <div class="card mb-2">
    <form class="card-body" onsubmit="return user_profile_confirm(event, this);" id="frm-profile">
      <h6>Profile Information</h6>
      <div class="row g-4">
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="info_name" id="info-name" class="form-control text-center"
                   value="{{$viewer->name}}" required
            />
            <label for="info-name">Name <b class="text-danger">*</b></label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="email" name="info_email" id="info-email" class="form-control text-center"
                   value="{{$viewer->email}}" required
            />
            <label for="info-email">Email <b class="text-danger">*</b></label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="info_phone" id="info-phone" class="form-control text-center"
                   value="{{$viewer->phone}}"
            />
            <label for="info-phone">Phone</label>
          </div>
        </div>
        <div class="col-md-3 acm-text-right">
          <button type="submit" class="btn btn-primary me-1">Submit</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card mb-4">
    <form class="card-body" onsubmit="return user_pwd_confirm(event, this);" id="frm-pwd">
      <h6>Password</h6>
      <div class="row g-4">
        <div class="col-md-3 d-none">
          <div class="form-floating form-floating-outline">
            <input type="text" name="pwd_code" id="pwd-code" class="form-control text-uppercase text-center"

            />
            <label for="pwd-code">Code Verify <b class="text-danger">*</b></label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="password" name="pwd_pwd1" id="pwd-pwd1" class="form-control text-center"
                   autocomplete="new-password"
                   required
            />
            <label for="pwd-pwd1">New Password <b class="text-danger">*</b></label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="password" name="pwd_pwd2" id="pwd-pwd2" class="form-control text-center"
                   autocomplete="new-password"
                   required
            />
            <label for="pwd-pwd2">New Password Confirmation<b class="text-danger">*</b></label>
          </div>
        </div>
        <div class="col-md-6 acm-text-right">
          <button type="button" class="btn btn-secondary me-1 d-none" onclick="user_code_confirm()">Get Code
          </button>
          <button type="submit" class="btn btn-primary me-1">Submit</button>
        </div>
      </div>
    </form>
  </div>

  <!-- modal confirm to save -->
  <div class="modal animate__animated animate__rollIn" id="modal_confirm_profile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Save Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to save these information?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="user_profile()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item"/>
        </div>
      </div>
    </div>
  </div>
  <div class="modal animate__animated animate__rollIn" id="modal_confirm_code" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to send new code verify?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="user_code()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item"/>
        </div>
      </div>
    </div>
  </div>
  <div class="modal animate__animated animate__rollIn" id="modal_confirm_pwd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to change new password?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="user_pwd()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item"/>
        </div>
      </div>
    </div>
  </div>
@endsection

