<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingMethodToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_method')->after('subtotal')->nullable();
            $table->string('shipping_method_name')->after('shipping_method')->nullable();
            $table->decimal('shipping_tax')->after('shipping')->default(0.00);

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
            $table->dropColumn('shipping_method_name');
            $table->dropColumn('shipping_method');
            $table->dropColumn('shipping_tax');
        });
    }
}
