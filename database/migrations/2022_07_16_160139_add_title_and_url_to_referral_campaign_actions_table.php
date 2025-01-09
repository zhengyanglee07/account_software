<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleAndUrlToReferralCampaignActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('referral_campaign_actions', function (Blueprint $table) {
            $table->string('title')->nullable()->after('points');
            $table->json('url')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referral_campaign_actions', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('url');
        });
    }
}
