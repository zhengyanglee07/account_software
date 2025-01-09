<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationDateBasedTriggers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_date_based_triggers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('automation_trigger_id')->unsigned();
            $table->foreign('automation_trigger_id')->references('id')->on('automation_trigger')->onDelete('cascade');

            $table->integer('execution_time_period');
            $table->string('execution_time_unit');
            $table->string('execution_time_direction');
            $table->string('target');
            $table->timestamp('target_specific_date')->nullable();
            $table->boolean('repeat_yearly');
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
        Schema::dropIfExists('automation_date_based_triggers');
    }
}
