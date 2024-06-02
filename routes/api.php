<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//tastevn
use App\Http\Controllers\tastevn\ApiController;

Route::post('/kas/cart-information', [ApiController::class, 'kas_cart_info']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
