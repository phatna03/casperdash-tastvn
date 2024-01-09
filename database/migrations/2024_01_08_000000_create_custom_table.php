<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
//    if (!Schema::hasTable('food_restaurants')) {
//      Schema::create('food_restaurants', function (Blueprint $table) {
//        $table->id();
//        $table->string('name');
//        $table->timestamps();
//      });
//    }
//
//    if (!Schema::hasTable('food_classes')) {
//      Schema::create('food_classes', function (Blueprint $table) {
//        $table->id();
//        $table->foreign('food_restaurant_id')->references('id')->on('food_restaurants');
//        $table->string('name');
//        $table->timestamps();
//      });
//    }
//
//    if (!Schema::hasTable('food_class_layers')) {
//      Schema::create('food_class_layers', function (Blueprint $table) {
//        $table->id();
//        $table->foreign('food_class_id')->references('id')->on('food_classes');
//        $table->string('name');
//        $table->timestamps();
//      });
//    }
//
//    if (!Schema::hasTable('food_scans')) {
//      Schema::create('food_scans', function (Blueprint $table) {
//        $table->id();
//        $table->text('url');
//        $table->foreign('food_class_id')->references('id')->on('food_classes');
//        $table->decimal(2, 3)->default(0);
//        $table->smallInteger('status')->default(1);
//        $table->smallInteger('total_layers')->default(0);
//        $table->smallInteger('total_errors')->default(0);
//        $table->date('date_food');
//        $table->dateTime('time_scan');
//        $table->longText('response')->nullable();
//        $table->timestamps();
//      });
//    }
//
//    if (!Schema::hasTable('food_scan_errors')) {
//      Schema::create('food_scan_errors', function (Blueprint $table) {
//        $table->id();
//        $table->foreign('food_scan_id')->references('id')->on('food_scans');
//        $table->foreign('food_class_layer_id')->references('id')->on('food_class_layers');
//        $table->timestamps();
//      });
//    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {

  }
};
