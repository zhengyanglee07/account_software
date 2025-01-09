<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('affiliate_userid')->unsigned();
            $table->foreign('affiliate_userid')->references('id')->on('affiliate_users')->onDelete('cascade');
            $table->string('affiliate_unique_link');
            $table->integer('total_click_for_link')->default(0)->nullable();
            $table->integer('current_paid_account')->default(0)->nullable();
            $table->integer('total_referrals')->default(0)->nullable();
            $table->integer('current_trial_account')->default(0)->nullable();
            $table->decimal('total_commission', 9, 2)->default(0.00);
            $table->decimal('total_pending_commission', 9, 2)->default(0.00);
            $table->decimal('total_withdrawal', 9, 2)->default(0.00);
			$table->decimal('current_balance',9, 2)->default(0.00);
			$table->decimal('commission_rate',9,1)->default(0.2);
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
        Schema::dropIfExists('affiliate_details');
    }
}
