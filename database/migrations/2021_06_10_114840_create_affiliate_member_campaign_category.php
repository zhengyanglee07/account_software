<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberCampaignCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_campaign_category', function (Blueprint $table) {
            $table->unsignedBigInteger('affiliate_member_campaign_id');
            $table->foreign('affiliate_member_campaign_id', 'amcc_am_campaign_id_foreign')->references('id')->on('affiliate_member_campaigns')->onDelete('cascade');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id', 'amcc_category_id_foreign')->references('id')->on('category')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_member_campaign_category');
    }
}
