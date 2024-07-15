@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Logs')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}"/>
  <link rel="stylesheet"
        href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css')}}"/>
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/pickr/pickr-themes.css')}}"/>
@endsection

@section('vendor-script')
  <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/pickr/pickr.js')}}"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.16/sorting/datetime-moment.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.21/dataRender/datetime.js"></script>
  <script type="text/javascript"
          src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
@endsection

@section('content')
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Logs</h4>

  <div class="card mb-4" id="datatable-listing">
    <div class="card-header border-bottom wrap-search-form">
      <h5 class="card-title">Search Conditions</h5>

      <form onsubmit="event.preventDefault(); return datatable_refresh();">
        <div class="d-flex justify-content-between align-items-center row py-1 gap-3 gap-md-0">
          <div class="col-md-6 mb-2">
            <div class="form-floating form-floating-outline">
              <div class="form-control acm-wrap-selectize" id="log-search-user">
                <select name="users"
                        data-value="user"
                        class="ajx_selectize multi_selectize" multiple onchange="datatable_refresh()">
                  <option value="">All</option>
                </select>
              </div>
              <label for="log-search-user">Users</label>
            </div>
          </div>
          <div class="col-md-6 mb-2 d-none">
            <div class="form-floating form-floating-outline">
              <div class="form-control acm-wrap-selectize" id="log-search-type">
                <select name="types" class="opt_selectize multi_selectize" multiple onchange="datatable_refresh()">
                  <option value="">All</option>
                  @foreach($pageConfigs['options_type'] as $k => $option)
                    <option value="{{$k}}">{{$option}}</option>
                  @endforeach
                </select>
              </div>
              <label for="log-search-type">Types</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-floating form-floating-outline">
              <div class="form-control acm-wrap-selectize" id="log-search-restaurant">
                <select name="restaurants"
                        data-value="restaurant"
                        class="ajx_selectize multi_selectize" multiple onchange="datatable_refresh()">
                  <option value="">All</option>
                </select>
              </div>
              <label for="log-search-restaurant">Restaurants</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-floating form-floating-outline">
              <div class="form-control acm-wrap-selectize" id="log-search-item">
                <select name="items" class="opt_selectize multi_selectize" multiple onchange="datatable_refresh()">
                  <option value="">All</option>
                  @foreach($pageConfigs['options_item'] as $k => $option)
                    <option value="{{$k}}">{{$option}}</option>
                  @endforeach
                </select>
              </div>
              <label for="log-search-item">Items</label>
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="form-floating form-floating-outline">
              <input type="text" class="form-control text-center date_time_picker" name="time_created"
                     id="log-search-time-created" autocomplete="off" data-value="last_and_current_day"
                     onchange="datatable_refresh()"/>
              <label for="log-search-time-created">Time created</label>
            </div>
          </div>
        </div>
      </form>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover">
        <thead class="table-light">
        <tr>
          <th></th>
          <th class="acm-width-150-min">Created At</th>
          <th>Log</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {

      //datatable
      datatable_listing = $('#datatable-listing table').DataTable(Object.assign(datatable_cfs, acmcfs.datatable_init));

    });

    var datatable_listing;
    var datatable_cfs = {
      "ajax": {
        'url': '{{url('datatable/logs')}}',
        "data": function (d) {
          d.users = $('#datatable-listing .wrap-search-form form select[name=users]').val();
          // d.types = $('#datatable-listing .wrap-search-form form select[name=types]').val();
          d.restaurants = $('#datatable-listing .wrap-search-form form select[name=restaurants]').val();
          d.items = $('#datatable-listing .wrap-search-form form select[name=items]').val();
          d.time_created = $('#datatable-listing .wrap-search-form form input[name=time_created]').val();
        },
      },
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-type', data.type);
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'created_at', name: 'created_at'},
        {data: 'text', name: 'text'},
      ],
      columnDefs: [
        {
          targets: 1,
          render: $.fn.dataTable.render.moment('YYYY-MM-DDTHH:mm:ss.SSSSZ', 'DD/MM/YY HH:mm:ss' )
        },
        {
          targets: 2,
          render: function(data, type, full, meta) {
            //custome
            var html = '<div>' + data + '</div>';

            if (full['item_type'] == 'restaurant_food_scan') {
              return ('<div class="cursor-pointer" onclick="sensor_food_scan_info(' + full['item_id'] + ')">' + $(html).text() + '</div>');
            }

            return $(html).text();
          }
        },
      ],
      buttons: [
        {
          text: '<i class="mdi mdi-reload me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Refresh</span>',
          className: 'add-new btn btn-dark waves-effect waves-light',
          attr: {
            'onclick': 'datatable_refresh()',
          }
        }
      ],
    };
  </script>
@endsection
