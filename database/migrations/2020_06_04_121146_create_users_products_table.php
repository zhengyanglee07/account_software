<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->String('productTitle');
            $table->text('productDescription')->nullable();
            $table->text('productImagePath')->nullable();
            $table->decimal('productPrice', 9, 2)->default(0.00);
            $table->decimal('productComparePrice', 9, 2)->nullable();
            $table->integer('availableStock')->nullable();
            $table->string('physicalProducts')->nullable();
            $table->string('continueSelling')->nullable();
            $table->string('reference_key')->nullable();
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
        Schema::dropIfExists('users_products');
    }
}
