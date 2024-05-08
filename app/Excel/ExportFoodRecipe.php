<?php

namespace App\Excel;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ExportFoodRecipe implements FromView
{

  private $items;

  public function setItems($items)
  {
    $this->items = $items;
  }

  public function view(): View
  {
    return view('tastevn.excels.export_food_recipe', [
      'items' => $this->items,
    ]);
  }
}
