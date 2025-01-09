<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNestedSetToAffiliateReferralLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_referral_logs', function (Blueprint $table) {
            $table->nestedSet();
            $table->string('subscription_lock_status')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable()->change();
            $table->bigInteger('affiliate_id')->unsigned()->nullable()->change();
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
            $table->dropNestedSet();
            $table->dropColumn('subscription_lock_status');
            $table->bigInteger('user_id')->unsigned()->nullable(false)->change();
            $table->bigInteger('affiliate_id')->unsigned()->nullable(false)->change();

        });
    }
}
