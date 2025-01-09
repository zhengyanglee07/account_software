<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralInviteeOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_invitee_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processed_contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('referral_campaign_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('refer_by');
            $table->foreign('refer_by')->references('id')->on('processed_contacts')->onDelete('cascade');
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
        Schema::dropIfExists('referral_invitee_orders');
    }
}
