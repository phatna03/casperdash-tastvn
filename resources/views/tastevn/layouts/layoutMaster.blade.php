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
@php
$version = '126';
@endphp
{{--css--}}
<link rel="stylesheet" href="{{asset('assets/vendor/libs/spinkit/spinkit.css')}}"/>
<link rel="stylesheet" href="{{asset('assets/vendor/libs/toastr/toastr.css')}}"/>
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}"/>
<link rel="stylesheet" href="{{url('custom/library/selectize/selectize.default.css')}}" />
<link rel="stylesheet" href="{{url('custom/library/selectize/selectize.css')}}" />
<link rel="stylesheet" href="{{url('custom/css/app.css?v=' . (int)date('Ymd') . $version)}}"/>
{{--js--}}
{{--<script src="{{url('custom/library/roboflow/roboflow.js')}}"></script>--}}
<script src="{{asset('assets/vendor/libs/toastr/toastr.js')}}"></script>
<script src="{{url('custom/library/currency/format_number.js')}}"></script>
<script src="{{url('custom/library/selectize/selectize.min.js')}}"></script>
<script src="{{url('custom/library/axios/axios.min.js')}}"></script>
<script src="{{url('custom/js/app.js?v=' . (int)date('Ymd') . $version)}}"></script>
<script src="{{url('custom/js/item.js?v=' . (int)date('Ymd') . $version)}}"></script>

<script type="text/javascript">
  //tastevn
  //configs
  var acmcfs = {
    link_base_url: '{{url('')}}',
    var_csrf: '{{csrf_token()}}',
    dev_mode: '{{$devMode}}',
    is_mobi: '{{$isMobi}}',

    message_title_info: '{{config('tastevn.message_title_info')}}',
    message_title_success: '{{config('tastevn.message_title_success')}}',
    message_title_error: '{{config('tastevn.message_title_error')}}',

    message_description_success_add: '{{config('tastevn.message_description_success_add')}}',
    message_description_success_update: '{{config('tastevn.message_description_success_update')}}',

    link_food_no_photo: '{{url('custom/img/no_photo.png')}}',

    html_loading: '<div class="text-center m-auto"><div class="sk-wave sk-primary"><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div></div></div>',

    timeout_default: 2000,
    timeout_quick: 500,
    timeout_notification: 5000,
    timeout_kitchen: {{$devMode == 'production' ? 500 : 2000}},

    notify_running: 0,

    rbf_model: null,
    rbf_auth: 1,
    rbf_js: 0,

    //speaker
    speaker: 0,
    @if($viewer)
      link_speaker: "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/casperdash/speaker_tester.mp3",
      link_speaker_tester: "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/casperdash/user_{{$viewer->id}}/speaker_tester.mp3",
      link_speaker_notify: "https://s3.ap-southeast-1.amazonaws.com/cargo.tastevietnam.asia/casperdash/user_{{$viewer->id}}/speaker_notify.mp3",
      user_role: '{{$viewer->role}}',
      uid: '{{$viewer->id}}',
    @else
      link_speaker: '{{url('')}}',
      link_speaker_tester: '{{url('')}}',
      link_speaker_notify: '{{url('')}}',
      user_role: '',
      uid: '',
    @endif

    datatable_init: {
      "pageLength": 25,
      "processing": true,
      "serverSide": true,

      dom:
        '<"row mx-2"' +
        '<"col-md-6"<"me-3 acm-filter-left"lf>>' +
        '<"col-md-6"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0 gap-3"B>>' +
        '<"col-sm-12 col-md-6 mt-1 mb-2"i>' +
        '<"col-sm-12 col-md-6 mt-1 mb-2"p>' +
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

  var notify_realtime = null;

  $(document).ready(function () {

    //auto bind
    bind_picker();
    bind_datad();

    bind_staff(acmcfs.user_role);

    @auth
    //notify
    // notification_newest();
    notify_realtime = setInterval(function () {
      notification_newest();
    }, acmcfs.timeout_notification);

    //modal 2
    $('.modal').on('show.bs.modal', function (event) {
      var idx = $('.modal:visible').length;
      $(this).css('z-index', 1040 + (10 * idx));
    });
    $('.modal').on('shown.bs.modal', function (event) {
      var idx = ($('.modal:visible').length) - 1; // raise backdrop after animation.
      $('.modal-backdrop').not('.stacked').css('z-index', 1039 + (10 * idx));
      $('.modal-backdrop').not('.stacked').addClass('stacked');
    });

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
    <div class="modal-dialog modal-xl acm-modal-xxl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title text-danger fw-bold"></h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="item" />

          <div class="row">
            <div class="col-lg-6 mb-2">
              <div class="food_info_select">
                <select class="ajx_selectize"
                        data-value="restaurant_parent"
                        name="restaurant_parent_id"
                        onchange="food_info_select(this)"
                >
                  <option value="">Please choose valid restaurant</option>
                </select>
              </div>

              <div class="food_info_img w-auto">
                <img class="w-100" />
              </div>
            </div>
            <div class="col-lg-3 mb-2">
              <div class="row">
                <div class="col-lg-12 mb-2">
                  <div class="text-primary fw-bold">+ Roboflow Ingredients</div>

                  <div class="food_info_ingredients food_roboflow"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-3 mb-2">
              <div class="row">
                <div class="col-lg-12 mb-2">
                  <div class="text-primary fw-bold">+ Recipe Ingredients</div>

                  <div class="food_info_ingredients food_recipe"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal animate__animated animate__zoomIn" id="modal_food_scan_info" aria-hidden="true">
    <div class="modal-dialog modal-xl acm-modal-xxl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title text-danger fw-bold"></h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

        </div>

        <div class="acm-modal-arrow acm-modal-arrow-prev" onclick="sensor_food_scan_info_action()">
          <img src="{{url('custom/img/arrow_left.png')}}" />
        </div>
        <div class="acm-modal-arrow acm-modal-arrow-next" onclick="sensor_food_scan_info_action(1)">
          <img src="{{url('custom/img/arrow_right.png')}}" />
        </div>

        <input type="hidden" name="popup_view_ids" />
        <input type="hidden" name="popup_view_id_itm" />
      </div>
    </div>
  </div>
  <div class="modal fade modal-second" id="modal_food_scan_info_update" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title fw-bold">Update</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form onsubmit="return sensor_food_scan_update(event, this);">
          <div class="modal-body">

          </div>
          <div class="modal-footer">
            <div class="wrap-btns">
              @include('tastevn.htmls.form_button_loading')
              <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Submit</button>
              <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- modal ingredient sync -->
  <div class="modal fade modal-second" id="modal_food_ingredient_sync" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Sync Ingredients</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="pt-0" onsubmit="return restaurant_food_sync(event, this);">
          <div class="modal-body">
            <select class="ajx_selectize" data-value="restaurant_parent" name="restaurant_parent_id" multiple>
              <option value="">Please choose restaurants</option>
            </select>
          </div>
          <div class="modal-footer">
            <div class="wrap-btns">
              @include('tastevn.htmls.form_button_loading')
              <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Submit</button>
              <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

            <input type="hidden" name="restaurant_parent_id" />
            <input type="hidden" name="food_id" />
            <input type="hidden" name="type" />
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- modal kas checker restaurant date -->
  <div class="modal fade" id="modal_checker_restaurant_date" aria-hidden="true"
{{--       data-bs-backdrop="static" data-bs-keyboard="false"--}}
  >
    <div class="modal-dialog modal-xl acm-modal-xxl" role="document">
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
@endauth
