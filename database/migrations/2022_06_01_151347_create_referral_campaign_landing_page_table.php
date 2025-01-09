<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralCampaignLandingPageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_campaign_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_campaign_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('landing_page_id');
            $table->foreign('landing_page_id')->references('id')->on('landingpage')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_campaign_landing_pages');
    }
}
