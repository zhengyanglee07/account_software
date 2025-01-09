<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOptionValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_option_id')->unsigned();
            $table->string('label')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order');
            $table->string('option')->nullable();
            $table->string('value_1')->nullable();
            $table->string('type_of_single_charge')->default('Default');
            $table->decimal('single_charge')->default(0.00);
            $table->timestamps();
        });
        Schema::table('product_option_values', function($table) {
            $table->foreign('product_option_id')->references('id')->on('product_options')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_option_values');
    }
}
