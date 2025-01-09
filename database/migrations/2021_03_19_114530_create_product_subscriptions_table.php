<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('users_products_id')->unsigned();
            $table->foreign('users_products_id')->references('id')->on('users_products')->onDelete('cascade');
            $table->string('price_id')->nullable();
            $table->string('display_name');
            $table->string('description')->nullable();
            $table->integer('amount');
            $table->integer('interval_count');
            $table->string('interval');
            $table->string('expiration')->default('never_expire');
            $table->integer('expiration_cycle')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_subscriptions');
    }
}
