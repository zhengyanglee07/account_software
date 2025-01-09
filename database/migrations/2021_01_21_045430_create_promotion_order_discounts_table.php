<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionOrderDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_order_discounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_discount_type')->nullable();
            $table->decimal('order_discount_value')->default(0);
            $table->decimal('order_discount_cap')->default(0);
            $table->string('requirement_type')->nullable();
            $table->decimal('requirement_value')->default(0);
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
        Schema::dropIfExists('promotion_order_discounts');
    }
}
