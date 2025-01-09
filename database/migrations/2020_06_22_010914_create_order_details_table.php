<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->integer('users_product_id')->nullable();
            // $table->bigInteger('users_product_id')->unsigned();
            // $table->foreign('users_product_id')->references('id')->on('users_products');
            $table->string('product_name')->nullable();
            $table->string('order_number')->nullable();
            $table->string('fulfillment_status');
            $table->dateTime('fulfilled_at')->nullable();
            $table->string('payment_status');
            $table->datetime('paid_at')->nullable();
            $table->decimal('unit_price', 9, 2)->default(0.00);
            $table->integer('quantity');
            $table->decimal('total', 9, 2)->default(0.00);
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
        Schema::dropIfExists('order_details');
    }
}
