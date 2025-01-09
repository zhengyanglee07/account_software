<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralSocialShareClickLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_social_share_click_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processed_contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('referral_campaign_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('copy');
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
        Schema::dropIfExists('referral_social_share_click_logs');
    }
}
