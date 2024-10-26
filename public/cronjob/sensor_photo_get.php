<?php

include_once 'db_connect.php';

use App\Api\SysRobo;

$limit = 1;
$page = 10;

SysRobo::photo_get([
  'limit' => $limit,
  'page' => $page,
]);
