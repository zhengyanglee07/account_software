<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductOptionUsersProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_option_users_product', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('users_product_id')->unsigned();
            $table->integer('product_option_id')->unsigned();
        });
        Schema::table('product_option_users_product', function($table) {
            $table->foreign('users_product_id')->references('id')->on('users_products')->onDelete('cascade');
            $table->foreign('product_option_id')->references('id')->on('product_options')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
