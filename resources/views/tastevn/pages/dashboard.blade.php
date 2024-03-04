@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Dashboard')

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
  <h4 class="py-3 mb-4"><span class="text-muted fw-light">Admin /</span> Dashboard</h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">List of restaurants</h5>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover" id="datatable-listing">
        <thead class="table-light">
        <tr>
          <th></th>
          <th>Name</th>
          <th>Total dishes</th>
          <th>Latest updated</th>
          <th></th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- offcanvas to add new item -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvas_add_item" aria-labelledby="offcanvas_add_item_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_add_item_label" class="offcanvas-title">Add Restaurant</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return restaurant_add(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-name" name="name" />
          <label for="add-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-s3-bucket" name="s3_bucket_name" />
          <label for="add-item-s3-bucket">S3 bucket name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="add-item-s3-address" name="s3_bucket_address" />
          <label for="add-item-s3-address">S3 bucket address <b class="text-danger">*</b></label>
        </div>
        <button class="btn btn-primary me-sm-3 me-1 data-submit" type="submit">Submit</button>
        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
      </form>
    </div>
  </div>
  <!-- offcanvas to edit item -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_edit_item" aria-labelledby="offcanvas_edit_item_label">
    <div class="offcanvas-header">
      <h5 id="offcanvas_edit_item_label" class="offcanvas-title">Edit Restaurant</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return restaurant_edit(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-name" name="name" />
          <label for="edit-item-name">Name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-s3-bucket" name="s3_bucket_name" />
          <label for="edit-item-s3-bucket">S3 bucket name <b class="text-danger">*</b></label>
        </div>
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-s3-address" name="s3_bucket_address" />
          <label for="edit-item-s3-address">S3 bucket address <b class="text-danger">*</b></label>
        </div>
        <button class="btn btn-primary me-sm-3 me-1 data-submit" type="submit">Submit</button>
        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
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
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="restaurant_delete(this)" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
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
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" onclick="restaurant_restore(this)" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
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
      "ajax": "{{ url('datatable/restaurant') }}",
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-name', data.name);
        $(row).attr('data-s3_bucket_name', data.s3_bucket_name);
        $(row).attr('data-s3_bucket_address', data.s3_bucket_address);
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'name', name: 'name'},
        {data: 'count_foods', name: 'count_foods'},
        {data: 'updated_at', name: 'updated_at'}
      ],
      columnDefs: [
        {
          targets: 1,
          render: function (data, type, full, meta) {
            return (
              '<div class="cursor-pointer" onclick="restaurant_info(' + full['id'] + ')">' +
              '<span>' +
              '<button type="button" class="btn btn-sm btn-icon btn-primary acm-mr-px-10" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="tooltip-primary" title="View Report">' +
              '<span class="mdi mdi-finance"></span>' +
              '</button>' +
              '</span>' +
              '<span>' + full['name'] + '</span>' +
              '</div>'
            );
          }
        },
        {
          targets: 3,
          render: $.fn.dataTable.render.moment('YYYY-MM-DDTHH:mm:ss.SSSSZ', 'DD/MM/YY H:mm:ss' )
        },
        {
          // Actions
          targets: 4,
          title: '',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {

            var user_role = $('#acmcfs_user_role').val();

            var html = '';
            var html_delete = '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_delete_item" onclick="restaurant_delete_confirm(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>';

            if (user_role == 'moderator') {
              html_delete = '';
            }

            return (
              '<div class="dropdown">' +
              '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
              '<div class="dropdown-menu">' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="restaurant_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>' +
              html_delete +
              '</div>' +
              '</div>'
            );
          }
        }
      ],
      buttons: [
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Restaurant</span>',
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
