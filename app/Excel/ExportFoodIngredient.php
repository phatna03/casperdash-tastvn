<?php

namespace App\Excel;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportFoodIngredient implements FromView
{

  private $items;

  public function set_items($items)
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
