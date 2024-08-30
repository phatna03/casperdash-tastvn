@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - KAS Checker')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}"/>


@endsection

@section('vendor-script')
  <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>


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
              <tr class="tr_restaurant_{{$restaurant->id}}">
                <td>
                  <div class="form-floating form-floating-outline">
                    <input type="text" id="kas-date-check" class="form-control text-center date_only"
                           name="date_check" autocomplete="off"
                      onchange="kas_date_check(this)"
                    />
                    <label for="kas-date-check">Date</label>
                  </div>
                </td>
                @foreach($restaurants as $restaurant)
                  <td class="td_restaurant td_restaurant_{{$restaurant->id}}" data-value="{{$restaurant->id}}">
                    <button type="button" onclick="kas_date_check_restaurant_data({{$restaurant->id}})" class="btn btn-sm btn-secondary d-none">
                      <div>
                        Total Orders: <b class="total_orders">0</b>
                      </div>
                      <div>
                        Total Photos: <b class="total_photos">0</b>
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
  </div>


@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();

    $('.page_main_content').removeClass('container-xxl');

    $(document).ready(function() {

      //date only
      if ($('.date_only').length) {
        $('.date_only').datepicker({
          autoclose: true,
          clearBtn: true,
          todayHighlight: true,
          format: 'dd/mm/yyyy',
          orientation: isRtl ? 'auto right' : 'auto left'
        });
        $('.date_only').val('');
      }

    });
  </script>
@endsection
