@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Sensors')

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
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Sensors</h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">List of sensors</h5>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover" id="datatable-listing">
        <thead class="table-light">
        <tr>
          <th class="acm-th-first"></th>
          <th>Name</th>
          <th class="@if($isMobi) d-none @endif">S3 Configuration</th>
          <th class="d-none">Roboflow Scan?</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- offcanvas to add new item -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas_add_item" aria-labelledby="offcanvas_add_item_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_add_item_label" class="offcanvas-title">Add Sensor</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return sensor_add(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-wrap-selectize" id="add-item-restaurant">
            <select class="ajx_selectize" name="restaurant" required
                  data-value="restaurant_parent"
                  data-placeholder="Please choose valid restaurant..."
            ></select>
          </div>
          <label for="add-item-restaurant">Restaurant <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-name" name="name" />
          <label for="add-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-s3-bucket" name="s3_bucket_name" />
          <label for="add-item-s3-bucket">S3 bucket name</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-s3-address" name="s3_bucket_address" />
          <label for="add-item-s3-address">S3 bucket address</label>
        </div>
        <div class="form-floating form-floating-outline mb-4 d-none">
          <div class="form-control" id="add-item-rbf-scan">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="rbf_scan" id="add-item-rbf-scan-yes" />
              <label class="form-check-label text-dark fw-bold" for="add-item-rbf-scan-yes">Yes</label>
            </div>
          </div>
          <label for="add-item-rbf-scan" class="text-danger">Roboflow scan?</label>
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
      <h5 id="offcanvas_edit_item_label" class="offcanvas-title">Edit Sensor</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return sensor_edit(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <div class="form-control acm-wrap-selectize" id="edit-item-restaurant">
            <select class="ajx_selectize" name="restaurant" required
                    data-value="restaurant_parent"
                    data-placeholder="Please choose valid restaurant..."
            ></select>
          </div>
          <label for="edit-item-restaurant">Restaurant <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-name" name="name" />
          <label for="edit-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-s3-bucket" name="s3_bucket_name" @if(!$viewer->is_admin()) disabled @endif />
          <label for="edit-item-s3-bucket">S3 bucket name</label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-s3-address" name="s3_bucket_address" @if(!$viewer->is_admin()) disabled @endif />
          <label for="edit-item-s3-address">S3 bucket address</label>
        </div>
        <div class="form-floating form-floating-outline mb-4 d-none">
          <div class="form-control" id="edit-item-rbf-scan">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="rbf_scan" id="edit-item-rbf-scan-yes" />
              <label class="form-check-label text-dark fw-bold" for="edit-item-rbf-scan-yes">Yes</label>
            </div>
          </div>
          <label for="edit-item-rbf-scan" class="text-danger">Roboflow scan?</label>
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
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="sensor_delete(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal confirm to restore item -->
  <div class="modal animate__animated animate__rollIn" id="modal_restore_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Restore Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="sensor_restore(this)">Submit</button>
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
      "ajax": "{{ url('datatable/sensor') }}",
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-name', data.name);
        $(row).attr('data-s3_bucket_name', data.s3_bucket_name);
        $(row).attr('data-s3_bucket_address', data.s3_bucket_address);
        $(row).attr('data-rbf_scan', data.rbf_scan);
        $(row).attr('data-restaurant_parent_id', data.restaurant_parent_id);
      },
      "columns": [
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'name', name: 'name'},
        {data: 's3_bucket_name', name: 's3_bucket_name'},
        {data: 's3_bucket_address', name: 's3_bucket_address'},
      ],
      columnDefs: [
        {
          targets: 0,
          render: function (data, type, full, meta) {
            var html = '';

            @if($viewer->is_admin())
              html += '<div class="d-inline-block dropdown acm-mr-px-5">' +
              '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
              '<div class="dropdown-menu">' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="sensor_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_delete_item" onclick="sensor_delete_prepare(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>' +
              '</div>' +
              '</div>';
            @endif

              html += '<div class="d-inline-block">' +
              '<span class="badge bg-secondary">' + full['DT_RowIndex'] + '</span>' +
              '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        {
          targets: 1,
          render: function (data, type, full, meta) {
            return (
              '<div>' +
              '<span>' +
              '<button type="button" onclick="sensor_kitchen(' + full['id'] + ')" class="btn btn-sm btn-icon btn-success acm-mr-px-5">' +
              '<span class="mdi mdi-chef-hat"></span>' +
              '</button>' +
              '</span>' +
              '<span>' +
              '<button type="button" onclick="sensor_info(' + full['id'] + ')" class="btn btn-sm btn-icon btn-primary acm-mr-px-5">' +
              '<span class="mdi mdi-eye"></span>' +
              '</button>' +
              '</span>' +
              '<span class="cursor-pointer" onclick="sensor_info(' + full['id'] + ')">' + full['name'] + '</span>' +
              '</div>'
            );
          }
        },
        {
          targets: 2,
          @if($isMobi)
          className: 'd-none',
          @endif
          render: function (data, type, full, meta) {
            var html = '';

            if (full['s3_bucket_name'] && full['s3_bucket_name'] != '') {
              html += '<div>' + full['s3_bucket_name'] + '</div>';
            }
            if (full['s3_bucket_address'] && full['s3_bucket_address'] != '') {
              html += '<div>' + full['s3_bucket_address'] + '</div>';
            }

            return ('<div class="cursor-pointer" onclick="sensor_info(' + full['id'] + ')">' + html + '</div>');
          }
        },
        {
          targets: 3,
          className: 'd-none',
          render: function (data, type, full, meta) {
            var html = '';

            if (full['rbf_scan'] && parseInt(full['rbf_scan'])) {
              html += '<i class="mdi mdi-check-circle text-success"></i>';
            }

            return ('<div>' + html + '</div>');
          }
        },
      ],
      buttons: [
          @if($viewer->is_admin())
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Sensor</span>',
          className: 'add-new btn btn-primary waves-effect waves-light acm-mr-px-10',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvas_add_item',
            'onclick': 'setTimeout(function () { $("#offcanvas_add_item form input[name=name]").focus(); }, 500)',
          }
        },
        {
          text: '<i class="mdi mdi-chef-hat me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Kitchens Dashboard</span>',
          className: 'add-new btn btn-success waves-effect waves-light acm-mr-px-10',
          attr: {
            'onclick': 'page_url("{{url('admin/kitchens')}}")',
          }
        },
          @endif
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
