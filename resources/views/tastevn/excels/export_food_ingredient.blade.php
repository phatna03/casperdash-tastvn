<?php
if (!isset($items) || !count($items)) {
  return;
}

?>

<table>
  @foreach($items as $item)
    <tr>
      <td>{{isset($item['c1']) ? $item['c1'] : ''}}</td>
      <td>{{isset($item['c2']) ? $item['c2'] : ''}}</td>
      <td>{{isset($item['c3']) ? $item['c3'] : ''}}</td>
      <td>{{isset($item['c4']) ? $item['c4'] : ''}}</td>
    </tr>
  @endforeach
</table>
