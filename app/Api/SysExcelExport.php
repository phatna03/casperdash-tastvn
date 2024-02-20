<?php

namespace App\Api;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SysExcelExport implements FromView
{
  private $rows;

  public function setRows($rows)
  {
    $this->rows = $rows;
  }

  public function view(): View
  {

    return view('tastevn.excels.export', [
      'rows' => $this->rows,
    ]);
  }
}
