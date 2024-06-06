@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Report: ' . $pageConfigs['item']->name)

@section('content')

  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Report: {{$pageConfigs['item']->name}}</h4>

  <h4 class="mb-2">
    <span class="badge bg-primary">{{$pageConfigs['item']->get_restaurant_parent()->name}}</span>
    <span class="badge bg-danger">{{date('d/m/Y H:i:s', strtotime($pageConfigs['item']->date_from)) . ' -> ' . date('d/m/Y H:i:s', strtotime($pageConfigs['item']->date_to))}}</span>
  </h4>

  <div class="card">
    <div class="card-header border-bottom">
      <h5 class="card-title m-0 text-uppercase">Report Information</h5>
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
