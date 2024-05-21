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
    Schema::create('notifications', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('type');
      $table->morphs('notifiable');
      $table->bigInteger('restaurant_id')->default(0);
      $table->bigInteger('food_id')->default(0);
      $table->bigInteger('object_type')->default(0);
      $table->bigInteger('object_id')->default(0);
      $table->text('data');
      $table->timestamp('read_at')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('notifications');
  }
};
