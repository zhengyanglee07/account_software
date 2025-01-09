<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDataTypeOfDecimalInPromotion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotion_free_shippings', function (Blueprint $table) {
            $table->decimal('requirement_value',20,2)->change();
        });
        Schema::table('promotion_order_discounts', function (Blueprint $table) {
            $table->decimal('requirement_value',20,2)->change();
            $table->decimal('order_discount_value',20,2)->change();
            $table->decimal('order_discount_cap',20,2)->change();
        });
        Schema::table('promotion_product_discounts', function (Blueprint $table) {
            $table->decimal('requirement_value',20,2)->change();
            $table->decimal('product_discount_value',20,2)->change();
            $table->decimal('product_discount_cap',20,2)->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promotion_free_shippings', function (Blueprint $table) {
            $table->decimal('requirement_value',9,2)->change();
        });
        Schema::table('promotion_order_discounts', function (Blueprint $table) {
            $table->decimal('requirement_value',9,2)->change();
            $table->decimal('order_discount_value',9,2)->change();
            $table->decimal('order_discount_cap',9,2)->change();
        });
        Schema::table('promotion_product_discounts', function (Blueprint $table) {
            $table->decimal('requirement_value',9,2)->change();
            $table->decimal('product_discount_value',9,2)->change();
            $table->decimal('product_discount_cap',9,2)->change();
        });
    }
}
