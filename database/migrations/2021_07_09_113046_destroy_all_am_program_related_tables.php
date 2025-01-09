<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DestroyAllAmProgramRelatedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('affiliate_member_program_participant_promotion');
        Schema::dropIfExists('affiliate_member_program_levels');
        Schema::dropIfExists('affiliate_member_program_participants');
        Schema::dropIfExists('affiliate_member_programs');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // no rollback on this
    }
}
