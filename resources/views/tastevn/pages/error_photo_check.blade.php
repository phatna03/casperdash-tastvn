@php
  $customizerHidden = 'customizer-hide';
  $configData = Helper::appClasses();
@endphp

@extends('tastevn/layouts/layoutMaster')

@section('title', 'Error - Photo Check')

@section('page-style')
  <!-- Page -->
  <link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-misc.css')}}">
@endsection


@section('content')
  <div class="misc-wrapper">
    <h1 class="mb-2 mx-2 text-uppercase">Photo Check</h1>

    <div class="d-flex justify-content-center mt-5">
      <div class="d-flex flex-column align-items-center">
        <form class="acm-width-600-min" onsubmit="return photo_check(event, this);">
          <div class="wrap-datas mb-4">
            <div class="text-dark fw-bold total">0</div>
          </div>

          <div class="wrap-ids mb-4">
            <div class="row datas"></div>
          </div>

          <div class="wrap-btns">
            @include('tastevn.htmls.form_button_loading')
            <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right" >Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@section('js_end')
  <script type="text/javascript">
    var $ = jQuery.noConflict();
    $(document).ready(function() {

      if (notify_realtime) {
        clearInterval(notify_realtime);
      }
    });

    function photo_check(evt, frm) {
      evt.preventDefault();
      var form = $(frm);
      form_loading(form);

      axios.post('/error/photo/scan', {

      })
        .then(response => {

          var html = '';
          // form.find('.wrap-ids .datas').empty();

          if (response.data.ids && response.data.ids.length) {
            response.data.ids.forEach(function (v, k) {
              html += '<div class="col-2">' + v +
                '</div>';
            });
          }

          form.find('.wrap-ids .datas').prepend(html);

          form.find('.wrap-datas .total').text(response.data.count);

          setTimeout(function () {
            form.find('.wrap-btns .btn-submit')[0].click();
          }, 1500);

        })
        .catch(error => {

          console.log('===========================================');
          console.log(error);

          if (error.response.data && Object.values(error.response.data).length) {
            Object.values(error.response.data).forEach(function (v, k) {
              message_from_toast('error', acmcfs.message_title_error, v);
            });
          }

        })
        .then(() => {

          form_loading(form, false);
        });

      return false;
    }

  </script>
@endsection
