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
    </tr>
  @endforeach
</table>
