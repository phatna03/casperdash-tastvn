@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Profile')

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
  <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Profile</h4>

  <div class="card mb-4">
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
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card mb-4">
    <form class="card-body" onsubmit="return user_pwd_confirm(event, this);" id="frm-pwd">
      <h6>Password</h6>
      <div class="row g-4">
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="pwd_code" id="pwd-code" class="form-control text-uppercase text-center"
                   required
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
        <div class="col-md-3 acm-text-right">
          <button type="button" class="btn btn-secondary me-sm-3 me-1" onclick="user_code_confirm()">Send Verify Code
          </button>
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card mb-4">
    <form class="card-body" onsubmit="return user_setting_confirm(event, this);" id="frm-setting">
      <h6>Settings</h6>
      <div class="row g-4">
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <div class="form-control text-center" id="setting-item-role">
            <span class="form-check d-inline-block acm-mr-px-10">
              <input name="setting_printer" class="form-check-input" type="radio" value="yes" id="setting-printer-yes"
                     @if($viewer->allow_printer) checked="checked" @endif />
              <label class="form-check-label" for="setting-printer-yes">
                yes
              </label>
            </span>
              <span class="form-check d-inline-block">
              <input name="setting_printer" class="form-check-input" type="radio" value="no" id="setting-printer-no"
                     @if(!$viewer->allow_printer) checked="checked" @endif />
              <label class="form-check-label" for="setting-printer-no">
                no
              </label>
            </span>
            </div>
            <label for="setting-phone">Enable Printer?</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="setting_ips_printer" id="setting-ips_printer" class="form-control text-center"
                   value="{{$viewer->ips_printer}}" />
            <label for="setting-ips_printer">Print using IPs</label>
            <div class="form-text">
              <div class="fw-bold">Separated by semicolon (;)</div>
              <div class="fw-bold">Example: 192.168.0.1;192.168.0.2;192.168.0.3</div>
            </div>
          </div>
        </div>
        <div class="col-md-6 acm-text-right">
          <button type="button" class="btn btn-secondary me-sm-3 me-1" onclick="user_test_printer()">Test Printer
          </button>
          <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
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
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="user_profile()" class="btn btn-primary" data-bs-dismiss="modal">Confirm
          </button>
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
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="user_code()" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
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
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="user_pwd()" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
          <input type="hidden" name="item"/>
        </div>
      </div>
    </div>
  </div>
  <div class="modal animate__animated animate__rollIn" id="modal_confirm_setting" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Save Settings?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to save these settings?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="user_setting()" class="btn btn-primary" data-bs-dismiss="modal">Confirm
          </button>
          <input type="hidden" name="item"/>
        </div>
      </div>
    </div>
  </div>
@endsection

