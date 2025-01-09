<?php

use App\Account;
use App\Notification;
use App\AccountPlanTotal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalSocialProofToAccountPlanTotals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_plan_totals', function (Blueprint $table) {
            $table->integer('total_social_proof')->default(0)->after('total_affiliate_member');
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
            $table->dropColumn('total_social_proof');
        });
    }
}
