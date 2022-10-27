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
    Schema::create('value_infos', function (Blueprint $table) {
      $table->id();
      $table->foreignId('column_id')
        ->constrained('column_infos')
        ->cascadeOnDelete();
      $table->string('values')->nullable();
      $table->enum('type', ['string', 'number', 'boolean', 'date']);
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
    Schema::dropIfExists('value_infos');
  }
};
