<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFreePlanToAffiliateReferralLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_referral_logs', function (Blueprint $table) {
            $table->boolean('isFreePlan')->after('referral_status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate_referral_logs', function (Blueprint $table) {
            $table->dropColumn('isFreePlan');
        });
    }
}
