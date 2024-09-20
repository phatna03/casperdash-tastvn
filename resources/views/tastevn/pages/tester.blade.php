@extends('tastevn/layouts/layoutMaster')

@section('title', 'TESTER')

@section('content')
<form class="form-control mt-4" onsubmit="return tester_import(event, this);">
  <div class="row">
    <div class="col-6">
      <input name="file" type="file"
             accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
             required onchange="excel_check(this)" class="form-control"
      />
    </div>

    <div class="col-6">
      <button type="submit" class="btn btn-primary btn-ok btn-submit acm-float-right">Submit</button>
    </div>
  </div>

</form>
@endsection

@section('js_end')
  <script type="text/javascript">
    $(document).ready(function() {
      // toggle_header();
    });


    function tester_import(evt, frm) {
      evt.preventDefault();
      var form = $(frm);

      form_loading(form);

      const formData = new FormData();
      formData.append('excel', form.find('input[type=file]')[0].files[0]);

      axios.post('/tester/post', formData)
        .then(response => {

          form_loading(form, false);

        })
        .catch(error => {
          console.log(error);
        });

      return false;
    }
  </script>
@endsection
