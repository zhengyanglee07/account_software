<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTextAndInstructionToReferralCampaignRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('referral_campaign_rewards', function (Blueprint $table) {
            $table->string('text')->default('Click here to view')->after('value');
            $table->json('instruction')->nullable()->after('text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referral_campaign_rewards', function (Blueprint $table) {
            $table->dropColumn('text');
            $table->dropColumn('instruction');
        });
    }
}
