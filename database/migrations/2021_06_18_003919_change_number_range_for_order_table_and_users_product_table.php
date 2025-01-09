<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNumberRangeForOrderTableAndUsersProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal',20,2)->change();
            $table->decimal('shipping',20,2)->change();
            $table->decimal('refund_shipping',20,2)->change();
            $table->decimal('shipping_tax',20,2)->change();
            $table->decimal('total',20,2)->change();
            $table->decimal('paided_by_customer',20,2)->change();
            $table->decimal('refunded',20,2)->change();
        });
        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('unit_price',20,2)->change();
            $table->decimal('total',20,2)->change();
        });
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->decimal('refundTotal',20,2)->change();
            $table->decimal('total',20,2)->change();
        });
        Schema::table('product_subscriptions', function (Blueprint $table) {
            $table->decimal('capped_at',20,2)->change();
            $table->decimal('amount',20,2)->change();
        });
        Schema::table('variant_details', function (Blueprint $table) {
            $table->decimal('price',20,2)->change();
            $table->decimal('comparePrice',20,2)->change();
        });
        Schema::table('product_options', function (Blueprint $table) {
            $table->decimal('total_charge_amount',20,2)->change();
        });
        Schema::table('product_option_values', function (Blueprint $table) {
            $table->decimal('single_charge',20,2)->change();
        });
        Schema::table('users_products', function (Blueprint $table) {
            $table->decimal('productPrice',20,2)->change();
            $table->decimal('productComparePrice',20,2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
}
