<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDiscountCapToNullableInPromotion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotion_order_discounts', function (Blueprint $table) {
            //
            $table->decimal('order_discount_cap')->nullable()->default(NULL)->change();
        });
        Schema::table('promotion_product_discounts', function (Blueprint $table) {
            $table->decimal('product_discount_cap')->nullable()->default(NULL)->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promotion_order_discounts', function (Blueprint $table) {
            $table->decimal('order_discount_cap')->nullable(false)->default(0.00)->change();
        });
        Schema::table('promotion_product_discounts', function (Blueprint $table) {
            $table->decimal('product_discount_cap')->nullable(false)->default(0.00)->change();
        });
    }
}
