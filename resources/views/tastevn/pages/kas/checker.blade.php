@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - KAS Checker')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}"/>

  <link rel="stylesheet" href="{{asset('assets/vendor/libs/swiper/swiper.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/css/pages/ui-carousel.css')}}" />

  <link rel="stylesheet" href="{{url('custom/library/lightbox/lc_lightbox.css')}}" />
  <link rel="stylesheet" href="{{url('custom/library/lightbox/minimal.css')}}" />
@endsection

@section('vendor-script')
  <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>

  <script src="{{asset('assets/vendor/libs/swiper/swiper.js')}}"></script>

  <script src="{{url('custom/library/lightbox/lc_lightbox.lite.js?v=101')}}"></script>
  <script src="{{url('custom/library/lightbox/alloy_finger.min.js')}}"></script>
@endsection

@section('content')
  @php
    $restaurants = $pageConfigs['restaurants'];

  @endphp

  <div class="row m-0">
    <div class="col-12 mb-1">
      <h4 class="position-relative w-100 mb-0">
        <span class="text-muted fw-light">Admin /</span> KAS Checker
      </h4>
    </div>

    <div class="col-12 mb-1">
      <div class="card">
        <div class="card-body p-2">
          <div class="card-datatable table-responsive">
            <table class="table table-bordered table-layout-fixed" id="table_checker">
              <thead>
              <tr>
                <th class="text-center">Date / Restaurants</th>
                @foreach($restaurants as $restaurant)
                  <th class="text-center text-bg-light text-dark">{{$restaurant->name}}</th>
                @endforeach
              </tr>
              </thead>
              <tbody>
              <tr>
                <td>
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="kas-date-check" class="form-control text-center date_only"
                           name="date_check" autocomplete="off" onchange="kas_date_check(this)"
                    />
                    <label for="kas-date-check" class="text-danger">Date</label>
                  </div>
                </td>
                @foreach($restaurants as $restaurant)
                  <td class="td_restaurant p-1 td_restaurant_{{$restaurant->id}}" data-value="{{$restaurant->id}}">
                    <button type="button" class="btn btn-sm btn-secondary p-1 w-50 acm-float-right total_photos d-none"
                            onclick="kas_date_check_restaurant_data_photo({{$restaurant->id}})" >
                      <div>
                        Total Photos: <div class="total_photos">0</div>
                      </div>
                    </button>

                    <button type="button" class="btn btn-sm btn-primary p-1 w-50 acm-float-left total_orders d-none"
                            onclick="kas_date_check_restaurant_data({{$restaurant->id}})" >
                      <div>
                        Total Bills: <div class="total_orders">0</div>
                      </div>
                    </button>
                  </td>
                @endforeach
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 mb-1">
      <div class="card">
        <div class="card-header p-2">
          <form onsubmit="return kas_date_check_search(event, this);" id="frm_checker_month">
            <div class="row">
              <div class="col-1 mb-1">
                <div class="form-floating form-floating-outline mb-1">
                  <div class="form-control acm-wrap-selectize" id="search-checker-month">
                    <select class="opt_selectize" name="month" required>
                      <option @if((int)date('m') == 1) selected="selected" @endif value="1">January</option>
                      <option @if((int)date('m') == 2) selected="selected" @endif value="2">February</option>
                      <option @if((int)date('m') == 3) selected="selected" @endif value="3">March</option>
                      <option @if((int)date('m') == 4) selected="selected" @endif value="4">April</option>
                      <option @if((int)date('m') == 5) selected="selected" @endif value="5">May</option>
                      <option @if((int)date('m') == 6) selected="selected" @endif value="6">June</option>
                      <option @if((int)date('m') == 7) selected="selected" @endif value="7">July</option>
                      <option @if((int)date('m') == 8) selected="selected" @endif value="8">August</option>
                      <option @if((int)date('m') == 9) selected="selected" @endif value="9">September</option>
                      <option @if((int)date('m') == 10) selected="selected" @endif value="10">October</option>
                      <option @if((int)date('m') == 11) selected="selected" @endif value="11">November</option>
                      <option @if((int)date('m') == 12) selected="selected" @endif value="12">December</option>
                    </select>
                  </div>
                  <label for="search-checker-month" class="text-danger">Month</label>
                </div>
              </div>

              <div class="col-1 mb-1">
                <div class="form-floating form-floating-outline mb-1">
                  <div class="form-control acm-wrap-selectize" id="search-checker-year">
                    <select class="opt_selectize" name="year" required>
                      @for($y = date('Y'); $y <= 2024; $y++)
                        <option @if(date('Y') == $y) selected="selected" @endif value="{{$y}}">{{$y}}</option>
                      @endfor
                    </select>
                  </div>
                  <label for="search-checker-year" class="text-danger">Year</label>
                </div>
              </div>

              <div class="col-1 mb-1">
                <div class="wrap-btns">
                  @include('tastevn.htmls.form_button_loading')
                  <button type="submit" class="btn btn-primary btn-ok btn-submit">
                    <i class="mdi mdi-image-search acm-mr-px-5"></i> Search
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="card-body p-2">
          <div class="card-datatable table-responsive">
            <table class="table table-bordered table-layout-fixed" id="table_checker_month">
              <thead>
              <tr>
                <th class="text-center">Date / Restaurants</th>
                @foreach($restaurants as $restaurant)
                  <th class="text-center text-bg-light text-dark">{{$restaurant->name}}</th>
                @endforeach
              </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();

    $('.page_main_content').removeClass('container-xxl');

    $(document).ready(function() {

      $('#frm_checker_month .wrap-btns button.btn-ok').click();

      //date only
      if ($('.date_only').length) {
        $('.date_only').datepicker({
          autoclose: true,
          clearBtn: true,
          todayHighlight: true,
          format: 'dd/mm/yyyy',
          orientation: isRtl ? 'auto right' : 'auto left'
        });
        $('.date_only').val('{{date('d/m/Y', strtotime('-1 days', time()))}}').trigger('change');
        // $('.date_only').val('27/08/2024').trigger('change');
      }

      //keyCode
      $(document).keydown(function(e) {
        // console.log(e.keyCode);
        if ($('#modal_food_scan_info').hasClass('show')) {
          if (e.keyCode == 37) {
            sensor_food_scan_info_action();
          } else if (e.keyCode == 39) {
            sensor_food_scan_info_action(1);
          }
        }
      });

    });
  </script>
@endsection
