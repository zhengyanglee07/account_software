<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberReferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_referrals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('affiliate_member_program_participant_id');
            $table->foreign('affiliate_member_program_participant_id', 'affiliate_member_referrals_program_participant_id')->references('id')->on('affiliate_member_program_participants')->onDelete('cascade');
            $table->foreignId('ecommerce_visitor_id')->constrained()->onDelete('cascade');

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
        Schema::dropIfExists('affiliate_member_referrals');
    }
}
