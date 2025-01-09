<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->unsigned();
			$table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
			$table->string('subscription_id');
			$table->integer('subscription_plan_id')->nullable();
            $table->string('status');
            $table->dateTime('current_plan_start')->nullable();
            $table->dateTime('current_plan_end')->nullable();
            $table->dateTime('trial_start')->nullable();
            $table->dateTime('trial_end')->nullable();
            $table->dateTime('cancel_at')->nullable();
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
        Schema::dropIfExists('subscriptions');
    }
}
