<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelyvaFulfillmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delyva_fulfillments', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('delyva_quotation_id')->unsigned();
            $table->foreign('delyva_quotation_id')->references('id')->on('delyva_quotations')->onDelete('cascade');

            $table->bigInteger('order_detail_id')->unsigned();
            $table->foreign('order_detail_id')->references('id')->on('order_details')->onDelete('cascade');

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
        Schema::dropIfExists('delyva_fulfillments');
    }
}
