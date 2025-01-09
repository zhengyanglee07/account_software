<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryHourToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_timeslot')->after('reference_key')->nullable();
            $table->string('delivery_date')->after('reference_key')->nullable();
            $table->string('delivery_hour_type')->after('reference_key')->default('default');
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
            $table->dropColumn('delivery_timeslot');
            $table->dropColumn('delivery_date');
            $table->dropColumn('delivery_hour_type');
        });
    }
}
