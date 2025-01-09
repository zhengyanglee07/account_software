<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralCampaignRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_campaign_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_campaign_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('reward_type_id');
            $table->foreign('reward_type_id')->references('id')->on('referral_campaign_reward_types')->onDelete('cascade');
            $table->string('title');
            $table->longText('value');
            $table->integer('point_to_unlock');
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
        Schema::dropIfExists('referral_campaign_rewards');
    }
}
