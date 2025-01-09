<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TriggeredStepsDropAutomationStepIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('triggered_steps', function (Blueprint $table) {
            $table->dropForeign('triggered_steps_automation_step_id_foreign');
            $table->dropColumn('automation_step_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('triggered_steps', function (Blueprint $table) {
            $table->bigInteger('automation_step_id')->unsigned();
            $table->foreign('automation_step_id')->references('id')->on('automation_steps')->onDelete('cascade');
        });
    }
}
