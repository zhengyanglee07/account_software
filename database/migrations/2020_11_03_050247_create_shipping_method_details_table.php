<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingMethodDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_method_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->bigInteger('shipping_method_id')->unsigned();
            // $table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->onDelete('cascade');
            $table->string('shipping_name')->nullable();
            $table->decimal('first_weight',9,2)->default(0.00);
            $table->decimal('first_weight_price',9,2)->default(0.00);
            $table->decimal('additional_weight',9,2)->default(0.00);
            $table->decimal('additional_weight_price',9,2)->default(0.00);
            $table->boolean('free_shipping')->default(false);
            $table->decimal('free_shipping_on')->default(0.00);
            $table->decimal('per_order_rate')->default(0.00);

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
        Schema::dropIfExists('shipping_method_details');
    }
}
