<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggeredStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('triggered_steps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('automation_id')->unsigned();
            $table->foreign('automation_id')->references('id')->on('automations')->onDelete('cascade');
            $table->bigInteger('automation_step_id')->unsigned();
            $table->foreign('automation_step_id')->references('id')->on('automation_steps')->onDelete('cascade');

            $table->string('batch');
            $table->longText('extra_properties')->nullable();
            $table->dateTime('execute_at');
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
        Schema::dropIfExists('triggered_steps');
    }
}
