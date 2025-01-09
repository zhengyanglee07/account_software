<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTierLockStatusToAffiliateReferralLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_referral_logs', function (Blueprint $table) {
            $table->string('tier_lock_status')->nullable();
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
            $table->dropColumn('tier_lock_status');
        });
    }
}
