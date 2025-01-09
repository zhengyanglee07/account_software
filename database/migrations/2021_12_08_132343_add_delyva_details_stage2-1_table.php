<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDelyvaDetailsStage21Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delyva_delivery_order_details', function (Blueprint $table) {
            $table->string('total_fee_currency')->nullable();
            $table->string('total_fee_amount')->nullable();
        });
        Schema::table('delyva_settings', function($table) {
            $table->string('item_type')->default("PARCEL")->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delyva_delivery_order_details', function (Blueprint $table) {
            $table->dropColumn('total_fee_currency');
            $table->dropColumn('total_fee_amount');
        });
        Schema::table('delyva_settings', function($table) {
            $table->string('item_type');
        });
        
    }
}
