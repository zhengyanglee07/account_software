<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessedContactPrizeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processed_contact_prize', function (Blueprint $table) {
            $table->foreignId('processed_contact_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('prize_id');
            $table->foreign('prize_id')->references('id')->on('referral_campaign_prizes')->onDelete('cascade');
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
        Schema::dropIfExists('processed_contact_prize');
    }
}
