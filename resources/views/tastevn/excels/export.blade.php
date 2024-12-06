<?php
if (!isset($rows) || !count($rows)) {
  return;
}

?>

<table>
  @foreach($rows as $row)
    <tr>
      @for($i=0;$i<=50;$i++)
        <td>{{isset($row[$i]) ? $row[$i] : ''}}</td>
      @endfor
    </tr>
  @endforeach
</table>
