<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//tastevn
use App\Http\Controllers\tastevn\ApiController;

//dev
Route::post('/dev/photo/get', [ApiController::class, 'rfs_get']);
Route::post('/dev/photo/check', [ApiController::class, 'rfs_check']);
//hop
Route::post('/food/predict', [ApiController::class, 'food_predict']);
//kas
Route::post('/kas/cart-information', [ApiController::class, 'kas_cart_info']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
