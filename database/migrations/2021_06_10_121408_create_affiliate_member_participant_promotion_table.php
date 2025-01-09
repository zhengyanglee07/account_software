<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberParticipantPromotionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_participant_promotion', function (Blueprint $table) {
            $table->bigInteger('affiliate_member_participant_id')->unsigned();
            $table->foreign('affiliate_member_participant_id', 'ampp_new_affiliate_member_participant_id_foreign')->references('id')->on('affiliate_member_participants')->onDelete('cascade');

            $table->bigInteger('promotion_id')->unsigned();
            $table->foreign('promotion_id', 'ampp_new_promotion_id_foreign')->references('id')->on('promotions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_member_participant_promotion');
    }
}
