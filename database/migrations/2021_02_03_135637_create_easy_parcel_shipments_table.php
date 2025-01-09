<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEasyParcelShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('easy_parcel_shipments', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            // service (before order is made)
            $table->string('service_detail');
            $table->string('service_id');
            $table->string('service_type');
            $table->string('service_name');
            $table->string('delivery');  // The day(s) to deliver the parcel

            // courier
            $table->string('courier_name');

            // shipment pricing
            $table->decimal('price', 9, 2)->default(0.00);
            $table->decimal('addon_price', 9, 2)->default(0.00);
            $table->decimal('shipment_price', 9, 2)->default(0.00);

            // additional cols after order is made
            $table->string('order_number')->nullable();
            $table->string('order_status')->nullable();

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
        Schema::dropIfExists('easy_parcel_shipments');
    }
}
