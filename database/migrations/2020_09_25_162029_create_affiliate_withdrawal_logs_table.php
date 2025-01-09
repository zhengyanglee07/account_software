<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateWithdrawalLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_withdrawal_logs', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('transaction_id');
			$table->bigInteger('affiliate_id')->unsigned();
			$table->foreign('affiliate_id')->references('id')->on('affiliate_users');
			$table->decimal('withdraw_amount',9,2)->default(0.00);
			$table->string('paypal_email')->nullable();
			$table->string('withdraw_date');
			$table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_withdrawal_logs');
    }
}
