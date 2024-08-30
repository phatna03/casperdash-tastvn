@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - KAS Dishes')

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
@endsection

@section('vendor-script')
  <script src="{{asset('assets/vendor/libs/moment/moment.js')}}"></script>
  <script src="{{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.16/sorting/datetime-moment.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.21/dataRender/datetime.js"></script>
@endsection

@section('content')
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> KAS Dishes</h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">List of KAS dishes</h5>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover" id="datatable-listing">
        <thead class="table-light">
        <tr>
          <th></th>
          <th>KAS Food</th>
          <th>System Found</th>
          <th>Valid Dish</th>
          <th>Latest updated</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  {{--  sync confirm  --}}
  <div class="modal animate__animated animate__rollIn" id="modal_sync_confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Sync KAS Foods</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to synchronize data from KAS API?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="kas_food_sync_confirm()">Confirm</button>
            <button type="button" class="btn btn-outline-secondary btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  {{--  sync food  --}}
  <div class="modal animate__animated animate__rollIn" id="modal_sync_food" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Valid Food</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form onsubmit="return kas_food_confirm(event, this);">
          <div class="modal-body">
            <div class="form-floating form-floating-outline">
              <div class="form-control acm-wrap-selectize" id="sync-update-food">
                <select class="ajx_selectize" name="food"
                        data-value="food" required
                        data-placeholder="dish name..."
                ></select>
              </div>
              <label for="sync-update-food" class="text-danger">Select Dish Valid</label>
            </div>
          </div>
          <div class="modal-footer">
            <div class="wrap-btns">
              @include('tastevn.htmls.form_button_loading')
              <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Confirm</button>
              <button type="button" class="btn btn-outline-secondary btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>
          </div>

          <input type="hidden" name="item" />
        </form>
      </div>
    </div>
  </div>

@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {

      //datatable
      datatable_listing = $('#datatable-listing').DataTable(Object.assign(datatable_cfs, acmcfs.datatable_init));

    });

    var datatable_listing;
    var datatable_cfs = {
      "ajax": "{{ url('datatable/kas/foods') }}",
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-name', data.item_name);
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'name', name: 'item_name'},
        {data: null},
        {data: null},
        {data: 'updated_at', name: 'updated_at'}
      ],
      columnDefs: [
        {
          targets: 1,
          render: function (data, type, full, meta) {
            return (
              '<div class="cursor-pointer" onclick="food_info(' + full['id'] + ')">' +
              '<span>' + full['item_name'] + '</span>' +
              '</div>'
            );
          }
        },
        {
          targets: 2,
          render: function (data, type, full, meta) {
            var html = '';

            if (full['web_food_id']) {
              html = '<span>' + full['web_food_name'] + '</span>';
            }

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            var html = '';

            html = '<button type="button" onclick="kas_food(' + full['id'] + ')" class="btn btn-sm btn-info p-1 acm-mr-px-5"><i class="mdi mdi-pencil"></i></button>';

            if (full['food_id']) {
              html += '<span>' + full['food_name'] + '</span>';
            }

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 4,
          render: $.fn.dataTable.render.moment('YYYY-MM-DDTHH:mm:ss.SSSSZ', 'DD/MM/YY HH:mm:ss' )
        },
      ],
      buttons: [
        {
          text: '<i class="mdi mdi-web-sync me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Sync KAS Foods</span>',
          className: 'add-new btn btn-primary waves-effect waves-light',
          attr: {
            'onclick': 'kas_food_sync()',
          }
        },
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
