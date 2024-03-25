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
    if (!Schema::hasTable('comments')) {
      Schema::create('comments', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id');
        $table->text('content');
        $table->string('object_type');
        $table->bigInteger('object_id');
        $table->smallInteger('edited')->default(0);
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('user_settings')) {
      Schema::create('user_settings', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id');
        $table->string('key');
        $table->text('value')->nullable();
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('texts')) {
      Schema::create('texts', function (Blueprint $table) {
        $table->id();
        $table->text('name');
        $table->smallInteger('creator_id')->default(0);
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('restaurant_food_scan_texts')) {
      Schema::create('restaurant_food_scan_texts', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('restaurant_food_scan_id');
        $table->bigInteger('text_id');
        $table->timestamps();
      });
    }

  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('comments');
    Schema::dropIfExists('user_settings');

    Schema::dropIfExists('texts');
    Schema::dropIfExists('restaurant_food_scan_texts');
  }
};
