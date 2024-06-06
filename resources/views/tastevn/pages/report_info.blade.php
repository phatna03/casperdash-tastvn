@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Report: ' . $pageConfigs['item']->name)

@section('content')

  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Report: {{$pageConfigs['item']->name}}</h4>

  <h4 class="mb-2">
    <div class="acm-float-right acm-ml-px-5">
      <span class="text-uppercase text-dark acm-fs-13 fw-bold">total photos: </span><span class="badge bg-secondary acm-fs-15">{{$pageConfigs['item']->total_photos}}</span>
    </div>

    <div class="overflow-hidden">
      <span class="badge bg-primary">{{$pageConfigs['item']->get_restaurant_parent()->name}}</span>
      <span class="badge bg-danger">{{date('d/m/Y H:i:s', strtotime($pageConfigs['item']->date_from)) . ' -> ' . date('d/m/Y H:i:s', strtotime($pageConfigs['item']->date_to))}}</span>
    </div>
  </h4>

  <div class="card">
    <div class="card-header border-bottom acm-clearfix">
      <div class="acm-float-right acm-ml-px-5">
        <div>
          <span class="text-uppercase text-dark acm-fs-13 fw-bold">Robot not found dishes: </span>
          <span class="badge bg-warning acm-fs-15 cursor-pointer" id="not_found_dishes"></span>
        </div>
      </div>

      <h5 class="card-title m-0 text-uppercase overflow-hidden">Report Information</h5>
    </div>
    <div class="card-body" id="wrap-datas">

    </div>
  </div>

@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {

      report_load('{{$pageConfigs['item']->id}}');
    });
  </script>
@endsection
