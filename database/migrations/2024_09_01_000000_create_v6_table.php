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

    if (!Schema::hasTable('restaurant_stats_dates')) {
      Schema::create('restaurant_stats_dates', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('restaurant_parent_id');
        $table->date('date');
        $table->bigInteger('total_files')->default(0);
        $table->bigInteger('total_photos')->default(0);
        $table->bigInteger('test_photos')->default(0);
        $table->bigInteger('total_bills')->default(0);
        $table->bigInteger('total_foods')->default(0);
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
