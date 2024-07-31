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

//    if (!Schema::hasTable('kas_sites')) {
//      Schema::create('kas_sites', function (Blueprint $table) {
//        $table->id();
//        $table->string('site_code');
//        $table->string('site_name');
//        $table->string('site_address')->nullable();
//        $table->string('site_tel')->nullable();
//        $table->string('site_email')->nullable();
//        $table->text('site_latitude')->nullable();
//        $table->text('site_longitude')->nullable();
//        $table->text('site_image')->nullable();
//        $table->timestamps();
//      });
//    }
//
//    if (!Schema::hasTable('kas_shifts')) {
//      Schema::create('kas_shifts', function (Blueprint $table) {
//        $table->id();
//        $table->string('shift_code');
//        $table->string('shift_name');
//        $table->text('shift_note')->nullable();
//        $table->timestamps();
//      });
//    }

    if (!Schema::hasTable('zalo_users')) {
      Schema::create('zalo_users', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id')->default(0);
        $table->string('zalo_user_id');
        $table->string('user_id_by_app')->nullable();
        $table->string('display_name')->nullable();
        $table->string('user_alias')->nullable();
        $table->string('user_phone')->nullable();
        $table->text('avatar')->nullable();
        $table->smallInteger('is_follower')->default(0);
        $table->smallInteger('shared_info')->default(0);
        $table->longText('datas')->nullable();
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('zalo_user_sends')) {
      Schema::create('zalo_user_sends', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id');
        $table->string('zalo_user_id');
        $table->string('type');
        $table->smallInteger('status')->default(0);
        $table->longText('params')->nullable();
        $table->longText('datas')->nullable();
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('cache')) {
      Schema::create('cache', function (Blueprint $table) {
        $table->string('key')->unique();
        $table->text('value');
        $table->integer('expiration');
      });
    }

    if (!Schema::hasTable('food_category_access')) {
      Schema::create('food_category_access', function (Blueprint $table) {
        $table->id();
        $table->bigInteger('user_id');
        $table->bigInteger('food_category_id');
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('zalo_users');
    Schema::dropIfExists('zalo_user_sends');

    Schema::dropIfExists('cache');
    Schema::dropIfExists('food_category_access');
  }
};
