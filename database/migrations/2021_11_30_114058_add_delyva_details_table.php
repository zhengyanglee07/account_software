<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDelyvaDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delyva_settings', function($table) {
            $table->string('item_type');
        });
        Schema::table('delyva_quotations', function($table) {
            $table->string('service_name');
        });
        Schema::table('delyva_delivery_orders', function($table) {
            $table->string('order_number')->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delyva_settings', function($table) {
            $table->dropColumn('item_type');
        });
        Schema::table('delyva_quotations', function($table) {
            $table->dropColumn('service_name');
            // $table->dropColumn('service_company_name');
        });
        Schema::table('delyva_delivery_orders', function($table) {
            $table->dropColumn('order_number');
        });
    }
}
