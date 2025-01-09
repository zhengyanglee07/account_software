<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderDetailIdToAffiliateMemberCommission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_member_commissions', function (Blueprint $table) {
            $table->foreignId('order_detail_id')->nullable()->constrained()->after('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate_member_commissions', function (Blueprint $table) {
            $table->dropForeign(['order_detail_id']);
            $table->dropColumn('order_detail_id');
        });
    }
}
