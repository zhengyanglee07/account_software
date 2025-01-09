<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionProductDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_product_discounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('product_discount_type')->nullable();
            $table->decimal('product_discount_value')->default(0);
            $table->decimal('product_discount_cap')->default(0);
            $table->integer('minimum_quantity')->default(0);
            $table->string('requirement_type')->nullable();
            $table->decimal('requirement_value')->default(0);
            $table->string('discount_target_type')->nullable();
            $table->json('target_value')->nullable();
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
        Schema::dropIfExists('promotion_product_discounts');
    }
}
