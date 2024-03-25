<?php

namespace App\Excel;

use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ImportDataFailed implements FromView
{
    private $rows;

    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function view(): View
    {

        return view('excel.import_data_failed', [
            'rows' => $this->rows,
        ]);
    }
}
