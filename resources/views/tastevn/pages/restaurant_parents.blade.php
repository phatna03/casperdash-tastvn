@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Restaurants')

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
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Restaurants</h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">List of restaurants</h5>
    </div>

    <div class="card-datatable table-responsive">
      <table class="table table-hover" id="datatable-listing">
        <thead class="table-light">
        <tr>
          <th class="acm-th-first"></th>
          <th>Name</th>
          <th class="@if($isMobi) d-none @endif">Total sensors</th>
          <th class="@if($isMobi) d-none @endif">Total dishes</th>
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
      <h5 id="offcanvas_edit_item_label" class="offcanvas-title">Edit Restaurant</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return restaurant_edit(event, this);">
        <div class="form-floating form-floating-outline mb-4">
          <input type="text" class="form-control" id="edit-item-name" name="name" />
          <label for="edit-item-name">Name <b class="text-danger">*</b></label>
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
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="restaurant_delete(this)">Submit</button>
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
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="restaurant_restore(this)">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="item" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal food import -->
  <div class="modal animate__animated animate__rollIn" id="modal_food_import" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <form onsubmit="return restaurant_food_import(event, this);">
          <div class="modal-header">
            <h4 class="modal-title">Update Food Category & Photo</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <a class="text-primary fw-bold" href="{{url('import_food_to_restaurant.xlsx')}}" download="" style="margin-bottom: 10px;">Download excel template file</a>

              <div class="col-12">
                <input name="file" type="file"
                       accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                       required onchange="excel_check(this)" class="form-control"
                />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="wrap-btns">
              @include('tastevn.htmls.form_button_loading')
              <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Submit</button>
              <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
            </div>

            <input type="hidden" name="restaurant_parent_id" />
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- modal food remove -->
  <div class="modal fade modal-second" id="modal_food_remove" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Remove Confirmation?</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col mb-12 mt-2">
              <div class="alert alert-danger">Are you sure you want to remove this dish from the restaurant?</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="button" class="btn btn-primary btn-ok btn-submit acm-float-right" onclick="restaurant_food_remove()">Submit</button>
            <button type="button" class="btn btn-outline-secondary btn-ok btn-cancel" data-bs-dismiss="modal">Cancel</button>
          </div>

          <input type="hidden" name="restaurant_parent_id" />
          <input type="hidden" name="food_id" />
        </div>
      </div>
    </div>
  </div>
  <!-- modal item info -->
  <div class="modal animate__animated animate__rollIn" id="modal_info_item" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl acm-modal-xxl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title text-danger fw-bold"></h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

        </div>

        <input type="hidden" name="restaurant_parent_id"/>
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
      },
      "columns": [
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'name', name: 'name'},
        {data: 'count_sensors', name: 'count_sensors'},
        {data: 'count_foods', name: 'count_foods'},
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
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="restaurant_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_food_import" onclick="restaurant_food_import_prepare(this)"><i class="mdi mdi-file-excel me-1"></i> Update Food Category & Photo</a>' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modal_delete_item" onclick="restaurant_delete_prepare(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>' +
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
            var html = '';
            html += '<div class="cursor-pointer" onclick="restaurant_info(' + full['id'] + ')">' +
              '<span>' +
              '<button type="button" class="btn btn-sm btn-icon btn-primary acm-mr-px-10">' +
              '<span class="mdi mdi-eye"></span>' +
              '</button>' +
              '</span>' +
              '<span class="text-dark">' + full['name'] + '</span>' +
              '</div>';

            return ('<div>' + html + '</div>');
          }
        },
        @if($isMobi)
        {
          targets: 2,
          className: 'd-none',
        },
        {
          targets: 3,
          className: 'd-none',
        },
        @endif
      ],
      buttons: [
        @if($viewer->is_admin())
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Restaurant</span>',
          className: 'add-new btn btn-primary waves-effect waves-light acm-mr-px-10',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvas_add_item',
            'onclick': 'setTimeout(function () { $("#offcanvas_add_item form input[name=name]").focus(); }, 500)',
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
