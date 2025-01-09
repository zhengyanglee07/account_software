<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_emails', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('referral_campaign_id')->unsigned();
            $table->foreign('referral_campaign_id')->references('id')->on('referral_campaigns')->onDelete('cascade');
            $table->string('type');
            $table->text('subject')->nullable();
            $table->longText('template')->nullable();
            $table->boolean('is_enabled')->default(true);
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
        Schema::dropIfExists('referral_emails');
    }
}
