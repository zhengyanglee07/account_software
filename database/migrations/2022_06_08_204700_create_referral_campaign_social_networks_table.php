<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralCampaignSocialNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_campaign_social_networks', function (Blueprint $table) {
            $table->foreignId('referral_campaign_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('social_network_id');
            $table->foreign('social_network_id')->references('id')->on('social_media_providers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_campaign_social_networks');
    }
}
