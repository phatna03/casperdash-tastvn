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
        $table->string('model_name')->nullable();
        $table->string('model_version')->nullable();
        $table->smallInteger('model_scan')->default(0);
        $table->bigInteger('count_sensors')->default(0);
        $table->bigInteger('count_foods')->default(0);
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('reports')) {
      Schema::create('reports', function (Blueprint $table) {
        $table->id();
        $table->text('name');
        $table->bigInteger('restaurant_parent_id');
        $table->dateTime('date_from');
        $table->dateTime('date_to');
        $table->bigInteger('total_foods')->default(0);
        $table->bigInteger('total_photos')->default(0);
        $table->bigInteger('total_points')->default(0);
        $table->decimal('point')->default(0);
        $table->enum('status', ['new', 'running', 'done'])->default('new');
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('report_foods')) {
      Schema::create('report_foods', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('report_id');
        $table->bigInteger('food_id');
        $table->bigInteger('total_photos')->default(0);
        $table->bigInteger('total_points')->default(0);
        $table->decimal('point')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('report_photos')) {
      Schema::create('report_photos', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('report_id');
        $table->bigInteger('restaurant_food_scan_id');
        $table->bigInteger('food_id')->default(0);
        $table->enum('status', ['passed', 'failed', 'edited'])->default('passed');
        $table->bigInteger('reporting')->default(1);
        $table->decimal('point')->default(1);
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
    Schema::dropIfExists('food_recipes');
    Schema::dropIfExists('restaurant_parents');

    Schema::dropIfExists('reports');
    Schema::dropIfExists('report_foods');
    Schema::dropIfExists('report_photos');
  }
};
