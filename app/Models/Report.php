<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
  use HasFactory;

  public $table = 'reports';

  protected $fillable = [
    'name',
    'restaurant_parent_id',
    'date_from',
    'date_to',
    'total_foods',
    'total_photos',
    'total_points',
    'point',
    'status',
    'deleted',
  ];

  public function get_type()
  {
    return 'report';
  }

  public function get_log()
  {
    return [
      'name' => $this->name,
      'restaurant_parent_id' => $this->restaurant_parent_id,
      'date_from' => $this->date_from,
      'date_to' => $this->date_to,
    ];
  }

  public function get_restaurant_parent()
  {
    return RestaurantParent::find($this->restaurant_parent_id);
  }

  public function start()
  {
    $sensors = Restaurant::select('id')
      ->where('deleted', 0)
      ->where('restaurant_parent_id', $this->restaurant_parent_id);

    $total_points = 0;

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->whereIn('restaurant_id', $sensors)
      ->whereIn('status', ['checked', 'edited', 'failed'])
      ->where('rbf_api', '<>', NULL)
      ->where('time_photo', '>=', $this->date_from)
      ->where('time_photo', '<=', $this->date_to)
      ->orderBy('id', 'asc')
      ->get();
    if (count($rows)) {
      foreach ($rows as $row) {

        $point = 1;
        $reporting = 1;
        $status = 'passed';

        switch ($row->status) {
          case 'checked':
            $total_points++;
            break;
          case 'edited':
            $total_points++;
            $point = 0;
            $status = 'failed';
            break;
          case 'failed':
            $reporting = 0;
            $point = 0;
            $status = 'failed';
            break;
        }

        $photo = ReportPhoto::create([
          'report_id' => $this->id,
          'restaurant_food_scan_id' => $row->id,
          'food_id' => $row->food_id,
          'reporting' => $reporting,
          'point' => $point,
          'status' => $status,
        ]);
      }
    }

    $foods = $this->get_restaurant_parent()->get_foods();
    if (count($foods)) {
      foreach ($foods as $food) {

        $total = ReportPhoto::where('food_id', $food->food_id)
          ->count();

        $point = ReportPhoto::where('food_id', $food->food_id)
          ->sum('point');

        ReportFood::create([
          'report_id' => $this->id,
          'food_id' => $food->food_id,

          'total_photos' => $total,
          'total_points' => $total,
          'point' => $point,
        ]);
      }
    }

    $this->update([
      'status' => 'running',
      'total_foods' => count($foods),
      'total_photos' => count($rows),
      'total_points' => $total_points,
    ]);
  }


}
