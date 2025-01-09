<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrackingNumberToOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->string('tracking_url')->nullable()->after('fulfillment_status');
            $table->string('tracking_number')->nullable()->after('fulfillment_status');
            $table->string('tracking_courier_service')->nullable()->after('fulfillment_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('tracking_url');
            $table->dropColumn('tracking_number');
            $table->dropColumn('tracking_courier_service');
        });
    }
}
