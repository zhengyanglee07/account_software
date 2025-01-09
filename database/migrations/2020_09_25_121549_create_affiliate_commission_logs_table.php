<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateCommissionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_commission_logs', function (Blueprint $table) {
			$table->bigIncrements('id');
            $table->bigInteger('referral_id')->unsigned();
            $table->foreign('referral_id')->references('id')->on('affiliate_referral_logs');
            $table->decimal('commission',9,2);
            $table->string('commission_status')->default('pending');
            $table->string('paid_date')->nullable();
            $table->string('credited_date')->nullable();
            $table->string('account_status')->nullable();
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
        Schema::dropIfExists('affiliate_commission_logs');
    }
}
