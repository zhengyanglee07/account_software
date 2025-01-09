<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcommerceAbandonedCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_abandoned_carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('visitor_id')->unsigned();
            $table->foreign('visitor_id')->references('id')->on('ecommerce_visitors')->onDelete('cascade');
            $table->json('product_detail')->nullable();
            $table->boolean('status')->default(false);
            $table->string('reference_key');
            $table->dateTime('view_at');
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
        Schema::dropIfExists('ecommerce_abandoned_carts');
    }
}
