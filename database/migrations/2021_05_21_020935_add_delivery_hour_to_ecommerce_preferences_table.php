<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryHourToEcommercePreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_preferences', function (Blueprint $table) {
            $table->integer('pre_order_from')->default(0)->after('require_account');
            $table->json('disabled_date')->nullable()->after('require_account');
            $table->json('delivery_hour')->nullable()->after('require_account');
            $table->string('delivery_hour_type')->default('default')->after('require_account');
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
            $table->dropColumn('pre_order_from');
            $table->dropColumn('disabled_date');
            $table->dropColumn('delivery_hour');
            $table->dropColumn('delivery_hour_type');
        });
    }
}
