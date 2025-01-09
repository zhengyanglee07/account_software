<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariantDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variant_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('users_products');
            $table->bigInteger('option_1_id')->nullable()->unsigned();
            $table->foreign('option_1_id')->references('id')->on('variant_values');
            $table->bigInteger('option_2_id')->nullable()->unsigned();
            $table->foreign('option_2_id')->references('id')->on('variant_values');
            $table->bigInteger('option_3_id')->nullable()->unsigned();
            $table->foreign('option_3_id')->references('id')->on('variant_values');
            $table->bigInteger('option_4_id')->nullable()->unsigned();
            $table->foreign('option_4_id')->references('id')->on('variant_values');
            $table->bigInteger('option_5_id')->nullable()->unsigned();
            $table->foreign('option_5_id')->references('id')->on('variant_values');
            $table->string('sku')->nullable();
            $table->decimal('price', 9, 2)->default(0.00);
            $table->decimal('comparePrice', 9, 2)->default(0.00);
            $table->string('image_url')->nullable();
            $table->boolean('is_visible')->default(1);
            $table->decimal('weight', 9, 2)->default(0.00);
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
        Schema::table('variant_details', function (Blueprint $table) {
            Schema::dropIfExists('variant_details');
        });
    }
}
