<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStorepickupInEcommercePreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_preferences', function (Blueprint $table) {
            $table->renameColumn('preperation_value','delivery_preperation_value');
            $table->renameColumn('is_preperation_time','delivery_is_preperation_time');
            $table->renameColumn('is_limit_order','delivery_is_limit_order');
            $table->renameColumn('pre_order_from','delivery_pre_order_from');
            $table->renameColumn('disabled_date','delivery_disabled_date');
            $table->integer('pickup_pre_order_from')->default(0)->after('require_account');
            $table->json('pickup_disabled_date')->nullable()->after('require_account');
            $table->json('pickup_hour')->nullable()->after('require_account');
            $table->boolean('is_enable_store_pickup')->default(false)->after('require_account');
            $table->integer('pickup_preperation_value')->default(0)->after('pickup_pre_order_from');
            $table->boolean('pickup_is_preperation_time')->default(false)->after('pickup_pre_order_from');
            $table->boolean('pickup_is_limit_order')->default(false)->after('pickup_pre_order_from');


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
            $table->dropColumn('pickup_pre_order_from');
            $table->dropColumn('pickup_disabled_date');
            $table->dropColumn('pickup_hour');
            $table->dropColumn('pickup_preperation_value');
            $table->dropColumn('pickup_is_preperation_time');
            $table->dropColumn('is_enable_store_pickup');

        });
    }
}
