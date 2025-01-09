<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->bigInteger('processed_contact_id')->unsigned();
            $table->foreign('processed_contact_id')->references('id')->on('processed_contacts')->onDelete('cascade');
            $table->bigInteger('product_subscription_id')->unsigned();
            $table->foreign('product_subscription_id')->references('id')->on('product_subscriptions')->onDelete('cascade');
            $table->string('subscription_id')->unique();
            $table->string('status');
            $table->dateTime('start_date');
            $table->dateTime('last_payment');
            $table->dateTime('next_payment');
            $table->dateTime('end_payment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_subscriptions');
    }
}
