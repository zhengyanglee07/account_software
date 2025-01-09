<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralSentEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_sent_emails', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('referral_email_id')->unsigned();
            $table->foreign('referral_email_id')->references('id')->on('referral_emails')->onDelete('cascade');
            $table->foreignId('processed_contact_id')->constrained()->onDelete('cascade');
            $table->integer('sent_email_id')->unsigned();
            $table->foreign('sent_email_id')->references('id')->on('sent_emails')->onDelete('cascade');
            $table->bigInteger('referral_campaign_reward_id')->unsigned()->nullable();
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
        Schema::dropIfExists('referral_sent_emails');
    }
}
