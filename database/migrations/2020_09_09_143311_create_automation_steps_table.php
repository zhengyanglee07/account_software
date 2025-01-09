<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_steps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->bigInteger('automation_id')->unsigned();
            $table->foreign('automation_id')->references('id')->on('automations')->onDelete('cascade');
            $table->bigInteger('automation_step_type_id')->unsigned();
            $table->foreign('automation_step_type_id')->references('id')->on('automation_step_types')->onDelete('cascade');
            $table->string('description');

            $table->longText('properties')->nullable();
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
        Schema::dropIfExists('automation_steps');
    }
}
