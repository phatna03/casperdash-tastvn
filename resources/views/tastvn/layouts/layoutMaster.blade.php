@isset($pageConfigs)
{!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
$configData = Helper::appClasses();
@endphp

@isset($configData["layout"])
@include((( $configData["layout"] === 'horizontal') ? 'tastvn.layouts.horizontalLayout' :
(( $configData["layout"] === 'blank') ? 'tastvn.layouts.blankLayout' :
(($configData["layout"] === 'front') ? 'tastvn.layouts.layoutFront' : 'tastvn.layouts.contentNavbarLayout') )))
@endisset

{{--tastvn--}}
<link rel="stylesheet" href="{{asset('assets/vendor/libs/toastr/toastr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/animate-css/animate.css')}}">
<link rel="stylesheet" href="{{url('tastvn/css/app.css')}}" />

<script src="{{asset('assets/vendor/libs/toastr/toastr.js')}}"></script>

@yield('js_end')

<div class="modal animate__animated animate__rollIn" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="exampleModalLabel5">Delete Confirmation?</h4>
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
        <button type="button" onclick="submit_ok()" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function submit_ok () {

    toastr.options = {
      autoDismiss: true,
      newestOnTop: true,
      positionClass: 'toast-bottom-right',
      onclick: null,
      rtl: isRtl
    };

    toastr['success']('Your changes have been updated successfully!', 'Success');
  }
</script>
