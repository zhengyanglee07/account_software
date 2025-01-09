<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderSubscriptionAddSubscriptionNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_subscriptions', function (Blueprint $table) {
            $table->string('subscription_name')->nullable()->after('subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_subscriptions', function (Blueprint $table) {
            $table->dropColumn('subscription_name');
        });
    }
}
