<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalFreeUserToAffiliateDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_details', function (Blueprint $table) {
            $table->integer('current_free_user')->default(0)->after('current_trial_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate_details', function (Blueprint $table) {
            $table->dropColumn('current_free_user');
        });
    }
}
