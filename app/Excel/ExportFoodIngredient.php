<?php

namespace App\Excel;

use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportFoodIngredient implements FromView
{

  private $items;

  public function setItems($items)
  {
    $this->items = $items;
  }

  public function view(): View
  {
    return view('tastevn.excels.export_food_ingredient', [
      'items' => $this->items,
    ]);
  }
}
