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

    if (!Schema::hasTable('kas_restaurants')) {
      Schema::create('kas_restaurants', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('restaurant_parent_id')->default(0);
        $table->string('restaurant_id');
        $table->string('restaurant_code');
        $table->string('restaurant_name');
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('kas_items')) {
      Schema::create('kas_items', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('kas_restaurant_id')->default(0);
        $table->string('item_id');
        $table->string('item_code');
        $table->string('item_name');
        $table->bigInteger('web_food_id')->default(0);
        $table->string('web_food_name')->nullable();
        $table->bigInteger('food_id')->default(0);
        $table->string('food_name')->nullable();
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('kas_staffs')) {
      Schema::create('kas_staffs', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('kas_restaurant_id')->default(0);
        $table->string('employee_id');
        $table->string('employee_code');
        $table->string('employee_name');
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('kas_tables')) {
      Schema::create('kas_tables', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('kas_restaurant_id');
        $table->string('area_id');
        $table->string('area_name');
        $table->string('table_id');
        $table->string('table_name');
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('kas_bills')) {
      Schema::create('kas_bills', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('kas_restaurant_id');
        $table->bigInteger('kas_table_id');
        $table->bigInteger('kas_staff_id');
        $table->string('bill_id');
        $table->date('date_create');
        $table->text('note')->nullable();
        $table->dateTime('time_create')->nullable();
        $table->dateTime('time_payment')->nullable();
        $table->string('status')->default('created');
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('kas_bill_orders')) {
      Schema::create('kas_bill_orders', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('kas_bill_id');
        $table->string('order_id');
        $table->string('status')->default('created');
        $table->text('note')->nullable();
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('kas_bill_order_items')) {
      Schema::create('kas_bill_order_items', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('kas_bill_order_id');
        $table->bigInteger('kas_item_id');
        $table->bigInteger('quantity')->default(1);
        $table->string('status')->default('created');
        $table->text('note')->nullable();
        $table->timestamps();
      });
    }


  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {

  }
};
