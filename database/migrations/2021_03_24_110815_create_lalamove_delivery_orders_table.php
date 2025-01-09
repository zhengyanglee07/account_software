<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLalamoveDeliveryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lalamove_delivery_orders', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('lalamove_quotation_id')->unsigned();
            $table->foreign('lalamove_quotation_id')->references('id')->on('lalamove_quotations')->onDelete('cascade');

            $table->string('customer_order_id')->nullable();
            $table->string('order_ref')->nullable();

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
        Schema::dropIfExists('lalamove_delivery_orders');
    }
}
