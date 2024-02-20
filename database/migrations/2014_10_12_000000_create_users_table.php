<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (!Schema::hasTable('users')) {
      Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('photo')->nullable();
        $table->string('phone')->nullable();
        $table->enum('role', ['superadmin', 'admin', 'moderator', 'user'])->default('user');
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->smallInteger('access_full')->default(1);
        $table->text('access_ids')->nullable();
        $table->longText('access_texts')->default('All');
        $table->dateTime('time_notification')->nullable();
        $table->text('note')->nullable();
        $table->bigInteger('creator_id')->default(0);
        $table->bigInteger('deleted')->default(0);
        $table->rememberToken();
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
