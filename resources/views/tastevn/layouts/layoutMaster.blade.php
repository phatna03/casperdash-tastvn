{{--themes--}}
@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
  $configData = Helper::appClasses();
@endphp

@isset($configData["layout"])
  @include((( $configData["layout"] === 'horizontal') ? 'tastevn.layouts.horizontalLayout' :
  (( $configData["layout"] === 'blank') ? 'tastevn.layouts.blankLayout' :
  (($configData["layout"] === 'front') ? 'tastevn.layouts.layoutFront' : 'tastevn.layouts.contentNavbarLayout') )))
@endisset

{{--tastevn--}}
{{--css--}}
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}"/>
<link rel="stylesheet" href="{{asset('assets/vendor/libs/toastr/toastr.css')}}"/>
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}"/>
<link rel="stylesheet" href="{{url('custom/library/selectize/selectize.default.css')}}" />
<link rel="stylesheet" href="{{url('custom/library/selectize/selectize.css')}}" />
<link rel="stylesheet" href="{{url('custom/css/app.css')}}"/>
{{--js--}}
<script src="{{asset('assets/vendor/libs/toastr/toastr.js')}}"></script>
<script src="{{url('custom/library/currency/format_number.js')}}"></script>
<script src="{{url('custom/library/selectize/selectize.min.js')}}"></script>
<script src="{{url('custom/js/app.js')}}"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script type="text/javascript">
  //tastevn
  //configs
  var acmcfs = {
    link_base_url: '{{url('')}}',
    var_csrf: '{{csrf_token()}}',

    message_title_info: '{{config('tastevn.message_title_info')}}',
    message_title_success: '{{config('tastevn.message_title_success')}}',
    message_title_error: '{{config('tastevn.message_title_error')}}',

    message_description_success_add: '{{config('tastevn.message_description_success_add')}}',
    message_description_success_update: '{{config('tastevn.message_description_success_update')}}',

    html_loading: '<div class="text-center m-auto"><div class="sk-wave sk-primary"><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div></div></div>',

    timeout_default: 2000,
    timeout_quick: 500,
    timeout_notification: 10000,

    datatable_init: {
      "pageLength": 25,
      "processing": true,
      "serverSide": true,

      dom:
        '<"row mx-2"' +
        '<"col-md-2"<"me-3"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0 gap-3"fB>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        sLengthMenu: 'Show _MENU_',
        search: '',
        searchPlaceholder: 'Search..'
      },
    }
  }

  $(document).ready(function () {

    bind_picker();
    bind_datad();

    @auth
    notification_newest();
    setInterval(function () {
      notification_newest();
    }, acmcfs.timeout_notification);
    @endauth
  });
</script>

@yield('js_end')

@auth
  <div class="modal animate__animated animate__rollIn" id="modal_logout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Logout Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to logout?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="auth_logout()" class="btn btn-primary" data-bs-dismiss="modal"> Confirm
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal animate__animated animate__rollIn" id="modal_food_info" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title text-danger fw-bold"></h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

        </div>
      </div>
    </div>
  </div>
  <div class="modal animate__animated animate__rollIn" id="modal_food_scan_info" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title text-danger fw-bold"></h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

        </div>

        <input type="hidden" name="item" />
      </div>
    </div>
  </div>
@endauth
