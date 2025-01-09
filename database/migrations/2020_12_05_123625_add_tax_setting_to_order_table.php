<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxSettingToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_product_include_tax')->default(false)->after('taxes');
            $table->boolean('is_shipping_fee_taxable')->default(false)->after('shipping');
            $table->decimal('tax_rate',9,2)->default(0.00)->after('taxes');
            $table->string('tax_name')->nullable()->after('shipping');
        });

        Schema::table('order_details', function(Blueprint $table){
            $table->boolean('is_taxable')->default(false)->after('total');
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
            $table->dropColumn('is_product_include_tax');
            $table->dropColumn('is_shipping_fee_taxable');
            $table->dropColumn('tax_rate');
            $table->dropColumn('tax_name');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('is_taxable');
        });
    }
}
