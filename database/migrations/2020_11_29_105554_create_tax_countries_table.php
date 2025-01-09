<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_countries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('country_name');
            $table->string('tax_name')->nullable();
            $table->decimal('country_tax',9,2)->default(0.00);
            $table->bigInteger('tax_setting_id')->unsigned();
            $table->foreign('tax_setting_id')->references('id')->on('taxes')->onDelete('cascade');
            $table->boolean('has_sub_region')->default(false);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_countries');
    }
}
