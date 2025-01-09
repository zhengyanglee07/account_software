<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashbackSaleChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cashback_sale_channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('cashback_id')->constrained('cashbacks')->onDelete('cascade');
            $table->foreignId('sale_channel_id')->constrained('sale_channels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cashback_sale_channels');
    }
}
