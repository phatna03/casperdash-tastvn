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
    if (!Schema::hasTable('food_recipes')) {
      Schema::create('food_recipes', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('food_id');
        $table->bigInteger('restaurant_parent_id')->default(0);
        $table->bigInteger('ingredient_id');
        $table->bigInteger('ingredient_quantity')->default(1);
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('restaurant_parents')) {
      Schema::create('restaurant_parents', function (Blueprint $table) {
        $table->id();
        $table->text('name');
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('food_recipes');
    Schema::dropIfExists('restaurant_parents');
  }
};
