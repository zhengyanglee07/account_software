<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberGroupAffiliateMemberParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_group_affiliate_member_participant', function (Blueprint $table) {
            $table->unsignedBigInteger('affiliate_member_group_id');
            $table->foreign('affiliate_member_group_id', 'amgamp_am_group_id_foreign')->references('id')->on('affiliate_member_groups')->onDelete('cascade');

            $table->unsignedBigInteger('affiliate_member_participant_id');
            $table->foreign('affiliate_member_participant_id', 'amgamp_am_participant_id_foreign')->references('id')->on('affiliate_member_participants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_member_group_affiliate_member_participant');
    }
}
