<div style="margin-bottom: 20px;">
  <div>[Test Printer] Cargo Restaurant - {{date('d/m/Y H:i:s')}}</div>
  <div>
    <div>Ingredients Missing:</div>
    <div>
      <div>- 1 Scramble egg - Trứng chiên khuấy</div>
      <div>- 1 Green Lettuce - Xà lách xanh</div>
      <div>- 1 Purple lettuce - Xà lách tím</div>
    </div>
  </div>
</div>

<div style="margin-bottom: 20px;">
  <div>[Test Printer] Cargo Restaurant - {{date('d/m/Y H:i:s')}}</div>
  <div>
    <div>Ingredients Missing:</div>
    <div>
      <div>- 1 Scramble egg - Trứng chiên khuấy</div>
      <div>- 1 Green Lettuce - Xà lách xanh</div>
      <div>- 1 Purple lettuce - Xà lách tím</div>
    </div>
  </div>
</div>

<script src="{{ asset(mix('assets/vendor/libs/jquery/jquery.js')) }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    window.print();
    setTimeout(function () {
      window.close();
    }, 2000);
    return false;
  });
</script>
