<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAffiliateIdIntoAffiliateCommissionLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_commission_logs', function (Blueprint $table) {
            $table->bigInteger('affiliate_id')->unsigned()->nullable()->after('referral_id');
            $table->foreign('affiliate_id')->references('id')->on('affiliate_users')->onDelete('cascade');
            $table->dropForeign('affiliate_commission_logs_referral_id_foreign');
            $table->foreign('referral_id')->references('id')->on('affiliate_referral_logs')->onDelete('cascade');
            $table->decimal('commission_rate',9,2)->default(0.00)->after('commission_status');
            $table->bigInteger('subscription_log_id')->unsigned()->nullable()->after('affiliate_id');
            $table->foreign('subscription_log_id')->references('id')->on('subscription_logs')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate_commission_logs', function (Blueprint $table) {
            $table->dropForeign('affiliate_commission_logs_affiliate_id_foreign');
            $table->dropColumn('affiliate_id');
            $table->dropColumn('commission_rate');
            $table->dropForeign('affiliate_commission_logs_subscription_log_id_foreign');
            $table->dropColumn('subscription_log_id');
        });
    }
}
