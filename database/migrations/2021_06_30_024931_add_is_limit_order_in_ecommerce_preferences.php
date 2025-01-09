<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLimitOrderInEcommercePreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_preferences', function (Blueprint $table) {
            $table->integer('preperation_value')->default(0)->after('pre_order_from');
            $table->boolean('is_preperation_time')->default(false)->after('pre_order_from');
            $table->boolean('is_limit_order')->default(false)->after('pre_order_from');
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
            $table->dropColumn('is_limit_order');
            $table->dropColumn('is_preperation_time');
            $table->dropColumn('preperation_value');
        });
    }
}
