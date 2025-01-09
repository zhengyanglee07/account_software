<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelyvaDeliveryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delyva_delivery_orders', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('delyva_quotation_id')->unsigned();
            $table->foreign('delyva_quotation_id')->references('id')->on('delyva_quotations')->onDelete('cascade');

            // $table->string('order_number')->nullable();
            // $table->foreign('order_number')->references('order_number')->on('order_details')->onDelete('cascade');

            $table->string('delyva_order_id')->nullable();
            // $table->string('delyva_invoice_id')->nullable();

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
        Schema::dropIfExists('delyva_delivery_orders');
    }
}
