<?php

namespace App\Api;
//lib
use App\Models\Restaurant;
use App\Models\RestaurantFoodScan;
use App\Models\RestaurantFoodScanMissing;

class SysDev
{
  public static function photo_check($pars = [])
  {
    $ids = [];

    $rows = RestaurantFoodScan::where('deleted', 0)
      ->where('local_storage', 1)
      ->select('id')
      ->orderBy('id', 'asc')
      ->limit(10)
      ->get();
    if (count($rows)) {
      foreach ($rows as $row) {
        $ids[] = $row->id;
      }
    }

    if (!count($ids)) {
      return false;
    }

    $ch = curl_init();
    $headers = [
      'Accept: application/json',
    ];

    $URL = "https://ai.block8910.com/api/dev/photo/check";

    $postData = [
      'ids' => $ids
    ];

    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $data = (array)json_decode($result);

    if (count($data) && isset($data['status']) && $data['status']) {
      $datas = (array)$data['datas'];
      if (count($datas)) {
        foreach ($datas as $itm) {
          $itm = (array)$itm;

          $row = RestaurantFoodScan::find((int)$itm['id']);
          if ($row && isset($itm['photo_url']) && !empty($itm['photo_url'])) {
            $row->update([
              'local_storage' => 0,
              'photo_url' => $itm['photo_url'],
            ]);
          }
        }
      }
    }

    return $data;
  }

  public static function photo_get($pars = [])
  {
    $min_id = 0;

    $row = RestaurantFoodScan::select('id')
      ->orderBy('id', 'desc')
      ->first();
    if ($row) {
      $min_id = $row->id;
    }

    if (!$min_id) {
      return false;
    }

    $ch = curl_init();
    $headers = [
      'Accept: application/json',
    ];

    $URL = "https://ai.block8910.com/api/dev/photo/get";

    $postData = [
      'min_id' => $min_id
    ];

    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $data = (array)json_decode($result);

    if (count($data) && isset($data['status']) && $data['status']) {
      $datas = (array)$data['datas'];
      if (count($datas)) {
        foreach ($datas as $itm) {
          $itm = (array)$itm;

          if (count($itm)) {
            unset($itm['id']);

            $row = RestaurantFoodScan::create($itm);

            $row->rfs_photo_predict([
              'notification' => false,
            ]);
          }
        }
      }
    }

    return $data;
  }
}
