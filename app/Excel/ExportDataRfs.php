<?php

namespace App\Excel;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportDataRfs implements FromView
{
    private $items;

    public function set_items($items)
    {
        $this->items = $items;
    }

    public function view(): View
    {

        return view('tastevn.excels.export_data_rfs', [
            'items' => $this->items,
        ]);
    }
}
