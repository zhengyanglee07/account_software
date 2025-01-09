<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLalamoveDeliveryOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lalamove_delivery_order_details', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('lalamove_delivery_order_id')->unsigned();
            $table->foreign('lalamove_delivery_order_id', 'lala_delivery_order_details_order_id_foreign')->references('id')->on('lalamove_delivery_orders')->onDelete('cascade');

            $table->string('driver_id')->nullable();
            $table->string('share_link')->nullable();
            $table->string('status');
            $table->longText('price');

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
        Schema::dropIfExists('lalamove_delivery_order_details');
    }
}
