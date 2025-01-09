<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberConditionGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_condition_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_member_campaign_id');
            $table->foreign('affiliate_member_campaign_id', 'amcg_am_campaign_id_foreign')->references('id')->on('affiliate_member_campaigns')->onDelete('cascade');
            $table->integer('priority');
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
        Schema::dropIfExists('affiliate_member_condition_groups');
    }
}
