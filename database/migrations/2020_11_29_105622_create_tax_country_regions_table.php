<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxCountryRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tax_country_regions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('country_id')->unsigned();
            $table->foreign('country_id')->references('id')->on('tax_countries')->onDelete('cascade');
            $table->string('sub_region_name')->default(null);
            $table->string('tax_name')->nullable();
            $table->decimal('tax_rate',9,2)->default(0.00);
            $table->string('tax_calculation')->default(null);
            $table->decimal('total_tax',9,2)->default(0.00);
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
        Schema::dropIfExists('tax_country_regions');
    }
}
