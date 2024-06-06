@extends('tastevn/layouts/layoutMaster')

@section('title', 'Admin - Report: ' . $pageConfigs['item']->name)

@section('vendor-style')
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')}}">
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/jquery-timepicker/jquery-timepicker.css')}}" />
  <link rel="stylesheet" href="{{asset('assets/vendor/libs/pickr/pickr-themes.css')}}" />

  <link rel="stylesheet" href="{{url('custom/library/lightbox/lc_lightbox.css')}}" />
  <link rel="stylesheet" href="{{url('custom/library/lightbox/minimal.css')}}" />
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
  <script type="text/javascript" src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

  <script src="{{url('custom/library/lightbox/lc_lightbox.lite.js')}}"></script>
  <script src="{{url('custom/library/lightbox/alloy_finger.min.js')}}"></script>
@endsection

@section('content')

  <h4 class="mb-2"><span class="text-muted fw-light">Admin /</span> Report: {{$pageConfigs['item']->name}}</h4>

  <h4 class="mb-2">
    <span class="badge bg-primary">{{$pageConfigs['item']->get_restaurant_parent()->name}}</span>
    <span class="badge bg-danger">{{date('d/m/Y H:i:s', strtotime($pageConfigs['item']->date_from)) . ' -> ' . date('d/m/Y H:i:s', strtotime($pageConfigs['item']->date_to))}}</span>
  </h4>

  <div class="card" id="wrap-datas">
    <div class="card-header border-bottom">
      <h5 class="card-title">Report Information</h5>


    </div>
  </div>

@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {

    });


  </script>
@endsection
