<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAffiliateTotalToAccountPlanTotals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_plan_totals', function (Blueprint $table) {
            $table->integer('total_affiliate_member')->default(0)->after('total_email');
            $table->integer('total_affiliate_campaign')->default(0)->after('total_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_plan_totals', function (Blueprint $table) {
            $table->dropColumn('affiliate_campaign_total');
            $table->dropColumn('affiliate_member_total');
        });
    }
}
