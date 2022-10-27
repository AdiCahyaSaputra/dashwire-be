<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('column_infos', function (Blueprint $table) {
      $table->id();
      $table->foreignUuid('table_id')
        ->constrained('table_infos')
        ->cascadeOnDelete();
      $table->string('column_name');
      $table->string('validation_rules');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('column_infos');
  }
};
