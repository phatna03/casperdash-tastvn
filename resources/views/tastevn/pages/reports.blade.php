@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Manage Reports')

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
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Manage Reports</h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">List of reports</h5>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover" id="datatable-listing">
        <thead class="table-light">
        <tr>
          <th></th>
          <th>Name</th>
          <th>Total Photos / Points / Dishes</th>
          <th>Date Range</th>
          <th class="d-none"></th>
          <th class="d-none"></th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- offcanvas to add new item -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas_add_item" aria-labelledby="offcanvas_add_item_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_add_item_label" class="offcanvas-title">Add Report</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return report_add(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-name" name="name" required />
          <label for="add-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-wrap-selectize" id="add-item-date">
            <input type="text" class="form-control text-center date_time_picker" name="dates" required
                   autocomplete="off" />
          </div>
          <label for="add-item-date">Date Range <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-wrap-selectize" id="add-item-restaurant">
            <select name="restaurant_parent_id" class="ajx_selectize" required
                    data-value="restaurant_parent" data-placeholder="Please choose restaurant..."
            >
            </select>
          </div>
          <label for="add-item-restaurant">Restaurant <b class="text-danger">*</b></label>
        </div>
        <div class="wrap-btns">
          @include('tastevn.htmls.form_button_loading')
          <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right" >Submit</button>
          <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="offcanvas">Cancel</button>
        </div>

      </form>
    </div>
  </div>
  <!-- offcanvas to edit item -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_edit_item" aria-labelledby="offcanvas_edit_item_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_edit_item_label" class="offcanvas-title">Edit User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return report_edit(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-name" name="name" required />
          <label for="edit-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-wrap-selectize" id="edit-item-date">
            <input type="text" class="form-control text-center date_time_picker" name="dates" required
                   autocomplete="off" />
          </div>
          <label for="edit-item-date">Date Range <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-wrap-selectize" id="edit-item-restaurant">
            <select name="restaurant_parent_id" class="ajx_selectize" required
                    data-value="restaurant_parent" data-placeholder="Please choose restaurant..."
            >
            </select>
          </div>
          <label for="edit-item-restaurant">Restaurant <b class="text-danger">*</b></label>
        </div>

        <div class="wrap-btns">
          @include('tastevn.htmls.form_button_loading')
          <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right" >Submit</button>
          <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="offcanvas">Cancel</button>
        </div>

        <input type="hidden" name="item" />
      </form>
    </div>
  </div>
  <!-- modal confirm to delete item -->
  <div class="modal animate__animated animate__rollIn" id="modal_delete_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Delete Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to delete this item?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="report_delete(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal confirm to start item -->
  <div class="modal animate__animated animate__rollIn" id="modal_start_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to start this report?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="report_start(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
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
      "ajax": "{{url('datatable/report')}}",
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-restaurant_parent_id', data.restaurant_parent_id);
        $(row).attr('data-date_from', data.date_from);
        $(row).attr('data-date_to', data.date_to);
        $(row).attr('data-name', data.name);
        $(row).attr('data-status', data.status);
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'name', name: 'name'},
        {data: 'total_photos', name: 'total_photos'},
        {data: 'status', name: 'status'},
        {data: 'date_from', name: 'date_from'},
        {data: 'date_to', name: 'date_to'},
      ],
      columnDefs: [
        {
          targets: 0,
          render: function (data, type, full, meta) {
            var html = '';

            html += '<div class="d-inline-block dropdown acm-mr-px-5">' +
            '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
            '<div class="dropdown-menu">' +
            '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="report_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>' +
            '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_delete_item" onclick="report_delete_confirm(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>' +
            '</div>' +
            '</div>';

            html += '<div class="d-inline-block">' +
            '<span class="badge bg-secondary">' + full['DT_RowIndex'] + '</span>' +
            '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 1,
          render: function (data, type, full, meta) {
            var html = '';

            if (full['status'] != 'new') {
              html += '<div class="cursor-pointer" onclick="report_info(' + full['id'] + ')">' +
                '<div class="acm-float-left">' +
                '<button type="button" class="btn btn-sm btn-icon btn-primary acm-mr-px-10">' +
                '<span class="mdi mdi-eye"></span>' +
                '</button>' +
                '</div>';
            }

            html += '<div class="acm-float-left">' +
              '<div class="text-dark">' + full['name'] + '</div>' +
              '<div class="text-primary acm-text-italic">(' + full['restaurant_name'] + ')</div>' +
              '</div>';

            html += '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 3,
          render: function (data, type, full, meta) {
            var html = '';

            html += full['date_from'] + '<br />' + full['date_to'];

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 2,
          render: function (data, type, full, meta) {
            var html = '';

            if (full['status'] == 'new') {
              html += '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_start_item" onclick="report_start_confirm(this)">start</button>';
            } else if (full['status'] == 'done') {
              html += '<div>' +
                '<div>' +
                '<span class="text-dark">' + full['total_photos'] + ' / ' + full['total_points'] + ' / ' + full['total_foods'] + '</span>' +
                '</div>' +
                '<div>' +
                '<span class="badge bg-success">' + full['status'] + '</span>' +
                '</div>' +
                '</div>';
            } else {
              html += '<div>' +
                '<div>' +
                '<span class="text-dark">' + full['total_photos'] + ' / ' + full['total_points'] + ' / ' + full['total_foods'] + '</span>' +
                '</div>' +
                '<div>' +
                '<span class="badge bg-info">' + full['status'] + '</span>' +
                '</div>' +
                '</div>';
            }

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 4,
          className: 'd-none',
        },
        {
          targets: 5,
          className: 'd-none',
        },
      ],
      buttons: [
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Report</span>',
          className: 'add-new btn btn-primary waves-effect waves-light acm-mr-px-10',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvas_add_item',
            'onclick': 'setTimeout(function () { $("#offcanvas_add_item form input[name=name]").focus(); }, 500)',
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
