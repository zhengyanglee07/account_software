<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocialPropertyToReferralCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('referral_campaigns', function (Blueprint $table) {
            $table->json('social_message')->nullable();
            $table->string('email_subject')->nullable();
            $table->json('email_message')->nullable();
            $table->boolean('social_network_enabled')->default(true);
            $table->boolean('share_email_enabled')->default(true);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referral_campaigns', function (Blueprint $table) {
            $table->dropColumn('social_message');
            $table->dropColumn('email_subject');
            $table->dropColumn('email_message');
            $table->dropColumn('social_network_enabled');
            $table->dropColumn('share_email_enabled');
        });
    }
}
