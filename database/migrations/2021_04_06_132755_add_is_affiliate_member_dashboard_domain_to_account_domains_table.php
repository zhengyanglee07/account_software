<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAffiliateMemberDashboardDomainToAccountDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_domains', function (Blueprint $table) {
            $table->boolean('is_affiliate_member_dashboard_domain')->after('is_verified')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_domains', function (Blueprint $table) {
            $table->dropColumn('is_affiliate_member_dashboard_domain');
        });
    }
}
