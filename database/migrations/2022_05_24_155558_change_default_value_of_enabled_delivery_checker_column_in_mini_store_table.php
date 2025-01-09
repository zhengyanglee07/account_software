<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDefaultValueOfEnabledDeliveryCheckerColumnInMiniStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mini_stores', function (Blueprint $table) {
            $table->boolean('is_enabled_delivery_checker')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mini_stores', function (Blueprint $table) {
            $table->boolean('is_enabled_delivery_checker')->default(true)->change();
        });
    }
}
