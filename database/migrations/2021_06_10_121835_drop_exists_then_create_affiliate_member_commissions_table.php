<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropExistsThenCreateAffiliateMemberCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('affiliate_member_commissions');

        Schema::create('affiliate_member_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('affiliate_member_campaign_id');
            $table->foreign('affiliate_member_campaign_id', 'amc_new_am_campaign_id_foreign')->references('id')->on('affiliate_member_campaigns')->onDelete('cascade');

            $table->unsignedBigInteger('affiliate_member_participant_id');
            $table->foreign('affiliate_member_participant_id', 'amc_new_am_participant_id_foreign')->references('id')->on('affiliate_member_participants')->onDelete('cascade');

            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->integer('level');
            $table->string('currency');
            $table->decimal('commission')->default(0.00);
            $table->string('affiliate_email');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_member_commissions');

        Schema::create('affiliate_member_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('affiliate_member_program_participant_id');
            $table->foreign('affiliate_member_program_participant_id', 'affiliate_member_commissions_participant_id_foreign')->references('id')->on('affiliate_member_program_participants')->onDelete('cascade');

            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->integer('level');
            $table->string('currency');
            $table->decimal('commission')->default(0.00);
            $table->string('affiliate_email');
            $table->timestamps();
        });
    }
}
