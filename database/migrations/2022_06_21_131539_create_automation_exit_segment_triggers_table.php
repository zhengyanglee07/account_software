<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationExitSegmentTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_exit_segment_triggers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('automation_trigger_id')->unsigned();
            $table->foreign('automation_trigger_id')->references('id')->on('automation_trigger')->onDelete('cascade');

            $table->bigInteger('segment_id')->unsigned()->nullable();
            $table->foreign('segment_id')->references('id')->on('segments')->onDelete('set null');
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
        Schema::dropIfExists('automation_exit_segment_triggers');
    }
}
