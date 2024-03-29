<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//tastevn
use App\Http\Controllers\tastevn\view\GuestController;

Route::post('/s3/bucket/callback', [GuestController::class, 's3_bucket_callback']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
