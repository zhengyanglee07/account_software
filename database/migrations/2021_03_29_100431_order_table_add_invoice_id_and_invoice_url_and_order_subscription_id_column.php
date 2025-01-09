<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderTableAddInvoiceIdAndInvoiceUrlAndOrderSubscriptionIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('order_subscription_id')->unsigned()->nullable()->after('processed_contact_id');
            $table->foreign('order_subscription_id')->references('id')->on('order_subscriptions')->onDelete('cascade');
            $table->string('invoice_id')->nullable();
            $table->string('invoice_url')->nullable();
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
            $table->dropForeign('orders_order_subscription_id_foreign');
            $table->dropColumn('order_subscription_id');
            $table->dropColumn('invoice_id');
            $table->dropColumn('invoice_url');
        });
    }
}
