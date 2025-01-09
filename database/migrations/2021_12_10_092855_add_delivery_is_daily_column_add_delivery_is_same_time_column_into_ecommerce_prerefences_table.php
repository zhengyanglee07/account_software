<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryIsDailyColumnAddDeliveryIsSameTimeColumnIntoEcommercePrerefencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_preferences', function (Blueprint $table) {
            $table->boolean('delivery_is_daily')->default(true);
            $table->boolean('delivery_is_same_time')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_preferences', function (Blueprint $table) {
            $table->boolean('delivery_is_daily');
            $table->boolean('delivery_is_same_time');
        });
    }
}
