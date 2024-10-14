@php
  $items = isset($items) ? (array)$items: [];
  if (!count($items)) {
      return;
  }
@endphp

<table>
  <tbody>
  <tr>
    <td>Ngày</td>
    <td>Tổng hình nhận từ SENSOR</td>
    <td>Tổng hình sau khi lọc bị trùng (do SENSOR chụp 2,3 hình 1 lần)</td>
    <td>Tổng hình IT test SENSOR</td>
    <td>Tổng hình món thực tế</td>
    <td>Tổng bill KAS trả về (chỉ tính bill thanh toán thành công)</td>
    <td>Tổng số món ăn (không tính món nước)</td>
  </tr>
  @php
    $count = 0;
      foreach($items as $item):
    $count++;
  @endphp
  <tr>
    <td>{{date('d/m/Y', strtotime($item['date']))}}</td>
    <td>{{$item['total_files']}}</td>
    <td>{{$item['total_photos']}}</td>
    <td>{{$item['test_photos']}}</td>
    <td>{{$item['total_photos'] - $item['test_photos']}}</td>
    <td>{{$item['total_bills']}}</td>
    <td>{{$item['total_foods']}}</td>
  </tr>
  @endforeach
  </tbody>
</table>
