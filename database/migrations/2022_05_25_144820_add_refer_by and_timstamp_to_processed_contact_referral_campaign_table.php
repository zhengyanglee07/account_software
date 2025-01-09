<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferByAndTimstampToProcessedContactReferralCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_contact_referral_campaign', function (Blueprint $table) {
            $table->unsignedBigInteger('refer_by')->nullable();
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
        Schema::table('processed_contact_referral_campaign', function (Blueprint $table) {
            $table->dropColumn('refer_by');
            $table->dropTimestamps();
        });
    }
}
