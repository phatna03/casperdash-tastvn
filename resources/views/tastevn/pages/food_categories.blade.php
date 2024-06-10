@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Categories')

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
  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Categories</h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">List of categories</h5>
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
      <h5 id="offcanvas_add_item_label" class="offcanvas-title">Add Category</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return food_category_add(event, this);">
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
      <h5 id="offcanvas_edit_item_label" class="offcanvas-title">Edit Category</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 h-100">
      <form class="pt-0" onsubmit="return food_category_edit(event, this);">
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
      "ajax": "{{ url('datatable/food-categories') }}",
      "createdRow": function( row, data, dataIndex ) {
        $(row).attr('data-id', data.id);
        $(row).attr('data-name', data.name);
      },
      "columns": [
        //stt
        {data: 'DT_RowIndex', name: 'DT_RowIndex' , orderable: false, searchable: false},
        {data: 'name', name: 'name'},
        {data: 'count_restaurants', name: 'count_restaurants'},
        {data: 'updated_at', name: 'updated_at'}
      ],
      columnDefs: [
        {
          targets: 3,
          render: $.fn.dataTable.render.moment('YYYY-MM-DDTHH:mm:ss.SSSSZ', 'DD/MM/YY HH:mm:ss' )
        },
        {
          // Actions
          targets: 4,
          title: '',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {

            var html = '';

            @if($viewer->is_admin())
              html = '<div class="dropdown">' +
              '<button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>' +
              '<div class="dropdown-menu">' +
              '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_edit_item" onclick="food_category_edit_prepare(this)"><i class="mdi mdi-pencil-outline me-1"></i> Edit</a>' +
              // '<a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#modalDeleteItem" onclick="food_category_delete_confirm(this)"><i class="mdi mdi-trash-can-outline me-1"></i> Delete</a>' +
              '</div>' +
              '</div>';
            @endif

            return ('<div>' + html + '</div>');
          }
        }
      ],
      buttons: [
        @if($viewer->is_admin())
        {
          text: '<i class="mdi mdi-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add Category</span>',
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
