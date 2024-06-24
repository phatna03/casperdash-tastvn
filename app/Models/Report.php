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

  public function get_items()
  {
    $items = [];

    $rows = ReportFood::query('report_foods')
      ->select('report_foods.food_id as food_id', 'foods.name as food_name',
        'report_foods.total_photos', 'report_foods.total_points', 'report_foods.point',
      )
      ->selectRaw('round(( report_foods.point/report_foods.total_points * 100 ),2) AS percentage')
      ->leftJoin('foods', 'foods.id', '=', 'report_foods.food_id')
      ->where('report_foods.report_id', $this->id)
      ->orderBy('percentage', 'desc')
      ->orderBy('report_foods.total_photos', 'desc')
      ->orderByRaw('TRIM(LOWER(foods.name))')
      ->get();
    if (count($rows)) {
      foreach ($rows as $row) {

        $ing_full = ReportPhoto::query('report_photos')
          ->select('report_photos.restaurant_food_scan_id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $this->id)
          ->where('restaurant_food_scans.food_id', $row['food_id'])
          ->where('restaurant_food_scans.missing_ids', NULL)
          ->where('restaurant_food_scans.status', 'checked')
          ->where('report_photos.status', 'passed')
          ->count();

        $ing_miss_right = ReportPhoto::query('report_photos')
          ->select('report_photos.restaurant_food_scan_id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $this->id)
          ->where('restaurant_food_scans.food_id', $row['food_id'])
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->where('restaurant_food_scans.status', 'checked')
          ->where('report_photos.status', 'passed')
          ->count();
        $wrong1s = ReportPhoto::query('report_photos')
          ->select('report_photos.restaurant_food_scan_id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $this->id)
          ->where('restaurant_food_scans.food_id', $row['food_id'])
          ->where('restaurant_food_scans.missing_ids', '<>', NULL)
          ->where('restaurant_food_scans.status', 'checked')
          ->where('report_photos.status', 'passed');

        $ing_miss_wrong_total = ReportPhoto::query('report_photos')
          ->select('report_photos.restaurant_food_scan_id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $this->id)
          ->where('restaurant_food_scans.food_id', $row['food_id'])
          ->where('restaurant_food_scans.status', 'edited')
          ->where('report_photos.reporting', 1)
          ->count();
        $wrong2s = ReportPhoto::query('report_photos')
          ->select('report_photos.restaurant_food_scan_id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $this->id)
          ->where('restaurant_food_scans.food_id', $row['food_id'])
          ->where('restaurant_food_scans.status', 'edited')
          ->where('report_photos.reporting', 1);

        $ing_miss_wrong_point = ReportPhoto::query('report_photos')
          ->select('report_photos.restaurant_food_scan_id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $this->id)
          ->where('restaurant_food_scans.food_id', $row['food_id'])
          ->where('restaurant_food_scans.status', 'edited')
          ->where('report_photos.reporting', 1)
          ->sum('report_photos.point');

        $ing_miss_wrong_failed = $ing_miss_wrong_total - $ing_miss_wrong_point;

        $not_found_total = ReportPhoto::query('report_photos')
          ->select('report_photos.restaurant_food_scan_id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $this->id)
          ->where('restaurant_food_scans.food_id', $row['food_id'])
          ->where('restaurant_food_scans.status', 'edited')
          ->where('report_photos.reporting', 0)
          ->count();
        $wrong3s = ReportPhoto::query('report_photos')
          ->select('report_photos.restaurant_food_scan_id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $this->id)
          ->where('restaurant_food_scans.food_id', $row['food_id'])
          ->where('restaurant_food_scans.status', 'edited')
          ->where('report_photos.reporting', 0);

        $not_found_point = ReportPhoto::query('report_photos')
          ->select('report_photos.restaurant_food_scan_id')
          ->leftJoin('restaurant_food_scans', 'restaurant_food_scans.id', '=', 'report_photos.restaurant_food_scan_id')
          ->where('report_photos.report_id', $this->id)
          ->where('restaurant_food_scans.food_id', $row['food_id'])
          ->where('restaurant_food_scans.status', 'edited')
          ->where('report_photos.reporting', 0)
          ->sum('report_photos.point');

        $not_found_failed = $not_found_total - $not_found_point;

        //subs
        $ing_miss_items = RestaurantFoodScanMissing::query('restaurant_food_scan_missings')
          ->select('ingredients.name as ingredient_name')
          ->selectRaw('SUM(restaurant_food_scan_missings.ingredient_quantity) as ingredient_total')
          ->leftJoin('ingredients', 'ingredients.id', '=', 'restaurant_food_scan_missings.ingredient_id')
          ->where(function ($q) use ($wrong1s, $wrong2s, $wrong3s) {
            $q->whereIn('restaurant_food_scan_missings.restaurant_food_scan_id', $wrong1s)
              ->orWhereIn('restaurant_food_scan_missings.restaurant_food_scan_id', $wrong2s)
              ->orWhereIn('restaurant_food_scan_missings.restaurant_food_scan_id', $wrong3s);
          })
          ->groupBy('restaurant_food_scan_missings.ingredient_quantity', 'ingredients.name')
          ->orderBy('ingredient_total', 'desc')
          ->orderByRaw('TRIM(LOWER(ingredients.name))')
          ->get();

        $items[] = [
          'food_id' => $row['food_id'],
          'food_name' => $row['food_name'],
          'total_photos' => $row['total_photos'],
          'total_points' => $row['total_points'],
          'point' => number_format($row['point'], 1, '.', ''),

          'ing_full' => $ing_full,
          'ing_miss_right' => $ing_miss_right,
          'ing_miss_wrong_total' => $ing_miss_wrong_total,
          'ing_miss_wrong_point' => number_format($ing_miss_wrong_point, 1, '.', ''),
          'ing_miss_wrong_failed' => $ing_miss_wrong_failed,

          'not_found_total' => $not_found_total,
          'not_found_point' => number_format($not_found_point, 1, '.', ''),
          'not_found_failed' => $not_found_failed,

          'ing_miss_items' => $ing_miss_items,
        ];
      }
    }

    return $items;
  }

  public function start()
  {
    $sensors = Restaurant::select('id')
      ->where('deleted', 0)
      ->where('restaurant_parent_id', $this->restaurant_parent_id);

    $total_points = 0;
    $report_points = 0;

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

            if ($row->food_id) {
              $total_points++;
            } else {
              $point = 0;
              $reporting = 0;
              $status = 'failed';
            }

            break;

          case 'edited':

            if ($row->food_id) {
              $total_points++;
            }

            $point = $row->rbf_error ? 0 : 1;
            $status = 'edited';

            if (!$row->rbf_predict && !$row->sys_predict) {
              $reporting = 0;
            }

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

        $report_points += $point;
      }
    }

    $foods = $this->get_restaurant_parent()->get_foods();
    if (count($foods)) {
      foreach ($foods as $food) {

        $total = ReportPhoto::where('food_id', $food->food_id)
          ->where('report_id', $this->id)
          ->count();

        $point = ReportPhoto::where('food_id', $food->food_id)
          ->where('report_id', $this->id)
          ->where('reporting', 1)
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
      'point' => $report_points,
    ]);
  }

  public function re_count()
  {
    $count = ReportPhoto::where('report_id', $this->id)
      ->whereIn('status', ['passed', 'edited'])
      ->where('food_id', '>', 0)
      ->count();

    $report_points = 0;

    $foods = ReportFood::where('report_id', $this->id)
      ->get();
    if (count($foods)) {
      foreach ($foods as $food) {

        $total = ReportPhoto::where('report_id', $this->id)
          ->where('food_id', $food->food_id)
          ->whereIn('status', ['passed', 'edited'])
          ->count();
        $point = ReportPhoto::where('report_id', $this->id)
          ->where('food_id', $food->food_id)
          ->whereIn('status', ['passed', 'edited'])
          ->sum('point');

        $food->update([
          'total_photos' => $total,
          'total_points' => $total,
          'point' => $point,
        ]);

        $report_points += $point;
      }
    }

    $this->update([
      'total_points' => $count,
      'point' => $report_points,
    ]);
  }
}
