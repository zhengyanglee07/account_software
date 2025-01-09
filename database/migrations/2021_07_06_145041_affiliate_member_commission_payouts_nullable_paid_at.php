<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AffiliateMemberCommissionPayoutsNullablePaidAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_member_commission_payouts', function (Blueprint $table) {
            $table->dateTime('paid_at')->nullable()->after('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate_member_commission_payouts', function (Blueprint $table) {
            // can't rollback to timestamp ady due to doctrine/dbal limitation
             $table->dateTime('paid_at')->after('status')->change();
        });
    }
}
