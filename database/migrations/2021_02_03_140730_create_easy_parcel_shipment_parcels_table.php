<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEasyParcelShipmentParcelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('easy_parcel_shipment_parcels', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('easy_parcel_shipment_id')->unsigned();
            $table->foreign('easy_parcel_shipment_id')->references('id')->on('easy_parcel_shipments')->onDelete('cascade');

            // after order payment is made
            $table->string('parcel_number')->nullable();
            $table->string('ship_status')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('awb')->nullable();
            $table->string('awb_id_link')->nullable();

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
        Schema::dropIfExists('easy_parcel_shipment_parcels');
    }
}
