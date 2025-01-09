<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyAbandonedAtAndProductDetailColumnInEcommerceAbandonedCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_abandoned_carts', function (Blueprint $table) {
            $table->dateTime('abandoned_at')->nullable()->change();
            $table->renameColumn('product_detail', 'cart_products');
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_abandoned_carts', function (Blueprint $table) {
            $table->dateTime('abandoned_at')->change();
            $table->renameColumn('cart_products', 'product_detail');
            $table->boolean('status')->default(false);
        });
    }
}
