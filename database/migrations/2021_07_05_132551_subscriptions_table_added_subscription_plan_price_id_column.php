<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubscriptionsTableAddedSubscriptionPlanPriceIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->bigInteger('subscription_plan_price_id')->unsigned()->nullable()->after('subscription_plan_id');
            $table->foreign('subscription_plan_price_id')->references('id')->on('subscription_plan_prices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign('subscriptions_subscription_plan_price_id_foreign');
            $table->dropColumn('subscription_plan_price_id');
        });
    }
}
