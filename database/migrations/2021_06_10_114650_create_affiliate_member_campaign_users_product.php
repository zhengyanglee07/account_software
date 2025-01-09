<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberCampaignUsersProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_campaign_users_product', function (Blueprint $table) {
            $table->unsignedBigInteger('affiliate_member_campaign_id');
            $table->foreign('affiliate_member_campaign_id', 'amcup_am_campaign_id_foreign')->references('id')->on('affiliate_member_campaigns')->onDelete('cascade');

            $table->unsignedBigInteger('users_product_id');
            $table->foreign('users_product_id', 'amcup_users_product_id_foreign')->references('id')->on('users_products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_member_campaign_users_product');
    }
}
