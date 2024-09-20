@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Settings')

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
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Settings</h4>

  <div class="card mb-4">
    <form class="card-body" onsubmit="return sys_setting_confirm(event, this);" id="frm-settings">
      <h6>1. AWS S3 </h6>
      <div class="row g-4">
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="s3_region" id="s3-region" class="form-control text-center"
                value="{{isset($pageConfigs['settings']['s3_region']) ? $pageConfigs['settings']['s3_region'] : ''}}"
                   disabled
            />
            <label for="s3-region">Region</label>
          </div>
        </div>
      </div>
      <hr class="my-4 mx-n4" />
      <h6>2. Roboflow</h6>
      <div class="row g-4">
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="rbf_api_key" id="rbf-api-key" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['rbf_api_key']) ? $pageConfigs['settings']['rbf_api_key'] : ''}}"
                   disabled
            />
            <label for="rbf-api-key">API Key</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="rbf_dataset_scan" id="rbf-dataset-scan" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['rbf_dataset_scan']) ? $pageConfigs['settings']['rbf_dataset_scan'] : ''}}"
                   disabled
            />
            <label for="rbf-dataset-scan">Dataset</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="rbf_dataset_ver" id="rbf-dataset-ver" class="form-control text-center fnumber"
                   value="{{isset($pageConfigs['settings']['rbf_dataset_ver']) ? (int)$pageConfigs['settings']['rbf_dataset_ver'] : ''}}"
            />
            <label for="rbf-dataset-ver">Version</label>
          </div>
        </div>
        <div class="col-md-3 d-none">
          <div class="form-floating form-floating-outline">
            <input type="text" name="rbf_food_confidence" id="rbf-food-confidence" class="form-control text-center fnumber"
                   value="{{isset($pageConfigs['settings']['rbf_food_confidence']) ? (int)$pageConfigs['settings']['rbf_food_confidence'] : '70'}}"
            />
            <label for="rbf-food-confidence">Minimum Confidence</label>
          </div>
        </div>
      </div>
      <hr class="my-4 mx-n4" />
      <h6>3. Mail Server</h6>
      <div class="row g-4">
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="mail_mailer" id="mail-mailer" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['mail_mailer']) ? $pageConfigs['settings']['mail_mailer'] : ''}}"
            />
            <label for="mail-mailer">Mailer</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="mail_host" id="mail-host" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['mail_host']) ? $pageConfigs['settings']['mail_host'] : ''}}"
            />
            <label for="mail-host">Host</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="mail_username" id="mail-username" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['mail_username']) ? $pageConfigs['settings']['mail_username'] : ''}}"
            />
            <label for="mail-username">Username</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="mail_password" id="mail-password" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['mail_password']) ? $pageConfigs['settings']['mail_password'] : ''}}"
            />
            <label for="mail-password">Password</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="mail_port" id="mail-port" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['mail_port']) ? $pageConfigs['settings']['mail_port'] : ''}}"
            />
            <label for="mail-port">Port</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="mail_encryption" id="mail-encryption" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['mail_encryption']) ? $pageConfigs['settings']['mail_encryption'] : ''}}"
            />
            <label for="mail-encryption">Encryption</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="mail_from_address" id="mail-from-address" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['mail_from_address']) ? $pageConfigs['settings']['mail_from_address'] : ''}}"
            />
            <label for="mail-from-address">From Address</label>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-floating form-floating-outline">
            <input type="text" name="mail_from_name" id="mail-from-name" class="form-control text-center"
                   value="{{isset($pageConfigs['settings']['mail_from_name']) ? $pageConfigs['settings']['mail_from_name'] : ''}}"
            />
            <label for="mail-from-name">From Name</label>
          </div>
        </div>
      </div>
      <div class="pt-4">
        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
        <button type="reset" class="btn btn-outline-secondary">Cancel</button>
      </div>
    </form>
  </div>

  <!-- modal confirm to save -->
  <div class="modal animate__animated animate__rollIn" id="modal_confirm_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Save Confirmation?</h4>
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
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="sys_setting()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
      </div>
    </div>
  </div>
@endsection

