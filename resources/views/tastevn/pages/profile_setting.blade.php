@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Profile Settings')

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
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Profile Settings</h4>

  <div class="card mb-2 d-none">
    <form class="card-body" onsubmit="return user_setting_confirm(event, this);" id="frm-setting">
      <div class="acm-clearfix">
        <div class="acm-float-right ml-2">
          <button type="button" class="btn btn-secondary me-1" onclick="user_test_sound()">Test Sound</button>
          <button type="button" class="btn btn-secondary me-1" onclick="user_test_printer()">Test Printer</button>
          <button type="submit" class="btn btn-primary me-1">Submit</button>
        </div>

        <h6>Settings</h6>
      </div>

      <div class="acm-clearfix">
        <div class="row g-4">
          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <div class="form-control text-center" id="setting-notify-sound">
              <span class="form-check d-inline-block acm-mr-px-10">
                <input name="setting_notify_sound" class="form-check-input" type="radio" value="yes" id="setting-notify-sound-yes"
                       @if((int)$viewer->get_setting('notify_sound')) checked="checked" @endif />
                <label class="form-check-label" for="setting-notify-sound-yes">
                  yes
                </label>
              </span>
                <span class="form-check d-inline-block">
                <input name="setting_notify_sound" class="form-check-input" type="radio" value="no" id="setting-notify-sound-no"
                       @if(!(int)$viewer->get_setting('notify_sound')) checked="checked" @endif />
                <label class="form-check-label" for="setting-notify-sound-no">
                  no
                </label>
              </span>
              </div>
              <label for="setting-notify-sound">Enable Sound Notification?</label>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-floating form-floating-outline">
              <div class="form-control text-center" id="setting-printer">
              <span class="form-check d-inline-block acm-mr-px-10">
                <input name="setting_allow_printer" class="form-check-input" type="radio" value="yes" id="setting-printer-yes"
                       @if((int)$viewer->get_setting('allow_printer')) checked="checked" @endif />
                <label class="form-check-label" for="setting-printer-yes">
                  yes
                </label>
              </span>
                <span class="form-check d-inline-block">
                <input name="setting_allow_printer" class="form-check-input" type="radio" value="no" id="setting-printer-no"
                       @if(!(int)$viewer->get_setting('allow_printer')) checked="checked" @endif />
                <label class="form-check-label" for="setting-printer-no">
                  no
                </label>
              </span>
              </div>
              <label for="setting-printer">Enable Printer?</label>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="card mb-4">
    <form class="card-body" onsubmit="return user_setting_notify_confirm(event, this);" id="frm-setting-notify">
      <div class="acm-clearfix">
        <div class="acm-float-right ml-2">
          <button type="button" class="btn btn-info me-1" onclick="page_url('{{url('guide/printer')}}')">Setup Printer</button>
          <button type="button" class="btn btn-info me-1" onclick="page_url('{{url('guide/speaker')}}')">Setup Speaker</button>
          <button type="submit" class="btn btn-primary me-1">Submit</button>
        </div>

        <h6>Notifications</h6>
      </div>

      <div class="acm-clearfix">
        <div class="row g-4">
          @foreach($sys_app->get_notifications() as $notify)
          <div class="col-md-12 notify_item" data-notify="{{$notify}}">
            <div class="form-floating form-floating-outline">
              <div class="form-control" id="setting-notify-{{$notify}}">
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="{{$notify . '_receive'}}" id="setting-notify-receive-{{$notify}}"
                             @if($viewer->get_setting($notify . '_receive') || $notify == 'photo_comment' || $notify == 'missing_ingredient') checked @endif
                             @if($notify == 'photo_comment' || $notify == 'missing_ingredient') disabled @endif
                      />
                      <label class="form-check-label" for="setting-notify-receive-{{$notify}}">Receive alert notification</label>
                    </div>
                  </div>
                  <div class="col-md-3">
                    @if($notify == 'missing_ingredient')
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="{{$notify . '_alert_speaker'}}" id="setting-notify-speaker-{{$notify}}"
                             @if($viewer->get_setting($notify . '_alert_speaker')) checked @endif />
                      <label class="form-check-label" for="setting-notify-speaker-{{$notify}}">Text-to-speech alert?</label>
                    </div>
                    @endif
                  </div>
                  <div class="col-md-3">
                    @if($notify == 'missing_ingredient')
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="{{$notify . '_alert_printer'}}" id="setting-notify-printer-{{$notify}}"
                             @if($viewer->get_setting($notify . '_alert_printer')) checked @endif />
                      <label class="form-check-label" for="setting-notify-printer-{{$notify}}">Printer alert?</label>
                    </div>
                    @endif
                  </div>
                  <div class="col-md-3">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="{{$notify . '_alert_email'}}" id="setting-notify-email-{{$notify}}"
                             @if($viewer->get_setting($notify . '_alert_email')) checked @endif />
                      <label class="form-check-label" for="setting-notify-email-{{$notify}}">Email alert?</label>
                    </div>
                  </div>
                </div>
              </div>
              <label for="setting-notify-{{$notify}}">{{config('tastevn.notify_setting_' . $notify)}}</label>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </form>
  </div>

  <!-- modal confirm to save -->
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
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="user_setting()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item"/>
        </div>
      </div>
    </div>
  </div>
  <div class="modal animate__animated animate__rollIn" id="modal_confirm_setting_notify" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Save Notification Settings?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to save these notification settings?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="user_setting_notify()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item"/>
        </div>
      </div>
    </div>
  </div>
@endsection

