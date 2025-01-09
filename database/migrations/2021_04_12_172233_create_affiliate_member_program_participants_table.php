<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberProgramParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_program_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_member_program_id');
            $table->foreign('affiliate_member_program_id', 'affiliate_program_participants_program_id')->references('id')->on('affiliate_member_programs')->onDelete('cascade');

            $table->unsignedBigInteger('affiliate_member_account_id')->nullable();
            $table->foreign('affiliate_member_account_id', 'affiliate_program_participants_member_account_id')->references('id')->on('affiliate_member_accounts')->onDelete('cascade');
            $table->string('status')->nullable();
            $table->nestedSet();
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
        Schema::table('affiliate_member_program_participants', function (Blueprint $table) {
            $table->dropNestedSet();
        });

        Schema::dropIfExists('affiliate_member_program_participants');
    }
}
