<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmendAutomationStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automation_steps', function (Blueprint $table) {
            $table->dropForeign('automation_steps_automation_step_type_id_foreign');
            $table->dropColumn('automation_step_type_id');
            $table->dropColumn('properties');

            $table->string('type')->after('description');
            $table->string('kind')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('automation_steps', function (Blueprint $table) {
// comment first, since automation_step_types table might be deleted later
//            $table->bigInteger('automation_step_type_id')->unsigned();
//            $table->foreign('automation_step_type_id')->references('id')->on('automation_step_types')->onDelete('cascade');
            $table->longText('properties')->nullable();
            $table->dropColumn('type');
            $table->dropColumn('kind');
        });
    }
}
