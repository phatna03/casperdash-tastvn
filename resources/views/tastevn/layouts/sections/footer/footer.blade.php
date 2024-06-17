@php
$containerFooter = ($configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme page_main_footer">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-3 flex-md-row flex-column">
      <div class="mb-2 mb-md-0">
        © <script>document.write(new Date().getFullYear())</script>,
        made with <span class="text-danger"><i class="tf-icons mdi mdi-heart"></i></span> by
        <b class="text-dark text-uppercase">Monadata</b>
      </div>
      <div class="d-none d-lg-inline-block">
        <a href="{{ config('tastevn.licenseUrl') ? config('tastevn.licenseUrl') : '#' }}" class="footer-link me-4" target="_blank">License</a>
      </div>
    </div>
  </div>
</footer>
<!--/ Footer-->
