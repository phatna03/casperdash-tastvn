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

    if (!Schema::hasTable('tastevn_items')) {
      Schema::create('tastevn_items', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('restaurant_parent_id')->default(0);
        $table->string('item_code');
        $table->text('item_name');
        $table->bigInteger('food_id')->nullable();
        $table->string('food_name')->nullable();
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
