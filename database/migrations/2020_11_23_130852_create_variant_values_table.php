<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariantValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variant_values', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('default')->default(0);
            $table->string('color')->default('#fff');
            $table->string('image_url')->nullable();
            $table->bigInteger('variant_id')->unsigned();
            $table->foreign('variant_id')->references('id')->on('variants')->onDelete('cascade');
            $table->string('variant_value')->nullable();
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
        Schema::table('variant_values', function (Blueprint $table) {
            Schema::dropIfExists('variant_values');
        });
    }
}
