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
    if (!Schema::hasTable('restaurants')) {
      Schema::create('restaurants', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('restaurant_parent_id')->default(0);
        $table->string('name');
        $table->bigInteger('count_foods')->default(0);
        $table->string('s3_bucket_name')->nullable();
        $table->string('s3_bucket_address')->nullable();
        $table->smallInteger('s3_checking')->default(0);
        $table->smallInteger('rbf_scan')->default(0);
        $table->bigInteger('creator_id')->default(0);
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('restaurant_access')) {
      Schema::create('restaurant_access', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id');
        $table->bigInteger('restaurant_id');
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('food_categories')) {
      Schema::create('food_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->bigInteger('count_restaurants')->default(0);
        $table->bigInteger('creator_id')->default(0);
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('foods')) {
      Schema::create('foods', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('photo')->nullable();
        $table->bigInteger('live_group')->default(3);
        $table->bigInteger('count_restaurants')->default(0);
        $table->bigInteger('creator_id')->default(0);
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('ingredients')) {
      Schema::create('ingredients', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('name_vi')->nullable();
        $table->bigInteger('creator_id')->default(0);
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('food_ingredients')) {
      Schema::create('food_ingredients', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('food_id');
        $table->bigInteger('restaurant_parent_id')->default(0);
        $table->enum('ingredient_type', ['core', 'additive'])->default('additive');
        $table->bigInteger('ingredient_id');
        $table->bigInteger('ingredient_quantity')->default(1);
        $table->integer('confidence')->default(50);
        $table->string('ingredient_color')->nullable();
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('restaurant_foods')) {
      Schema::create('restaurant_foods', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('restaurant_id');
        $table->bigInteger('food_category_id')->default(0);
        $table->bigInteger('food_id');
        $table->text('photo')->nullable();
        $table->integer('live_group')->default(3);
        $table->string('model_name')->nullable();
        $table->string('model_version')->nullable();
        $table->bigInteger('creator_id')->default(0);
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('restaurant_food_scans')) {
      Schema::create('restaurant_food_scans', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('restaurant_id');
        $table->bigInteger('food_category_id')->default(0);
        $table->bigInteger('food_id')->default(0);
        $table->integer('local_storage')->default(0);
        $table->text('photo_url');
        $table->text('photo_name');
        $table->text('photo_ext');
        $table->smallInteger('confidence')->default(0);
        $table->string('found_by')->nullable();
        $table->enum('status', ['new', 'failed', 'scanned', 'checked', 'edited']);
        $table->text('note')->nullable();
        $table->text('text_ids')->nullable();
        $table->longText('text_texts')->nullable();
        $table->timestamp('time_photo')->nullable();
        $table->timestamp('time_scan')->nullable();
        $table->timestamp('time_end')->nullable();
        $table->decimal('total_seconds')->default(0);
        $table->text('missing_ids')->nullable();
        $table->longText('missing_texts')->nullable();
        $table->bigInteger('sys_predict')->default(0);
        $table->smallInteger('sys_confidence')->default(0);
        $table->bigInteger('usr_predict')->default(0);
        $table->bigInteger('rbf_predict')->default(0);
        $table->smallInteger('rbf_confidence')->default(0);
        $table->smallInteger('rbf_retrain')->default(0);
        $table->longText('rbf_api')->nullable();
        $table->longText('rbf_api_js')->nullable();
        $table->text('rbf_version')->nullable();
        $table->smallInteger('rbf_model')->default(0);
        $table->longText('rbf_api_1')->nullable();
        $table->longText('rbf_api_2')->nullable();
        $table->bigInteger('is_marked')->default(0);
        $table->bigInteger('is_resolved')->default(0);
        $table->longText('usr_edited')->nullable();
        $table->bigInteger('deleted')->default(0);
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('restaurant_food_scan_missings')) {
      Schema::create('restaurant_food_scan_missings', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('restaurant_food_scan_id');
        $table->bigInteger('ingredient_id');
        $table->bigInteger('ingredient_quantity')->default(1);
        $table->string('ingredient_type')->default('additive');
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('settings')) {
      Schema::create('settings', function (Blueprint $table) {
        $table->id();
        $table->string('key');
        $table->text('value')->nullable();
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('bugs')) {
      Schema::create('bugs', function (Blueprint $table) {
        $table->id();
        $table->string('type');
        $table->text('file')->nullable();
        $table->text('line')->nullable();
        $table->text('message')->nullable();
        $table->text('params')->nullable();
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('restaurants');
    Schema::dropIfExists('restaurant_access');

    Schema::dropIfExists('food_categories');
    Schema::dropIfExists('foods');
    Schema::dropIfExists('ingredients');
    Schema::dropIfExists('food_ingredients');

    Schema::dropIfExists('restaurant_foods');
    Schema::dropIfExists('restaurant_food_scans');
    Schema::dropIfExists('restaurant_food_scan_missings');

    Schema::dropIfExists('settings');
    Schema::dropIfExists('bugs');
  }
};
