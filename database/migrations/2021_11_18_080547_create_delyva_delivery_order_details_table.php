<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelyvaDeliveryOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delyva_delivery_order_details', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('delyva_delivery_order_id')->unsigned();
            $table->foreign('delyva_delivery_order_id')->references('id')->on('delyva_delivery_orders')->onDelete('cascade');

            $table->string('serviceCode')->nullable();
            $table->string('consignmentNo')->nullable();
            $table->string('invoiceId')->nullable();
            $table->string('itemType');
            $table->smallInteger('statusCode');
            $table->string('status');


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
        Schema::dropIfExists('delyva_delivery_order_details');
    }
}
