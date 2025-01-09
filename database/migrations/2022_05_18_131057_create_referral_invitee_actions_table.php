<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralInviteeActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_invitee_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_campaign_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('action_type_id');
            $table->foreign('action_type_id')->references('id')->on('referral_campaign_action_types')->onDelete('cascade');
            $table->integer('points');
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
        Schema::dropIfExists('referral_invitee_actions');
    }
}
