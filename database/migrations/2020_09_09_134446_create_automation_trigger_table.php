<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationTriggerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_trigger', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('automation_id')->unsigned();
            $table->foreign('automation_id')->references('id')->on('automations')->onDelete('cascade');
            $table->bigInteger('trigger_id')->unsigned();
            $table->foreign('trigger_id')->references('id')->on('triggers')->onDelete('cascade');
            $table->bigInteger('segment_id')->unsigned()->nullable();
            $table->foreign('segment_id')->references('id')->on('segments')->onDelete('set null');

            $table->string('description');
            $table->longText('properties')->nullable();
            $table->bigInteger('automation_provider_id')->unsigned()->default(1);
            $table->foreign('automation_provider_id')->references('id')->on('automation_providers')->onDelete('cascade');
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
        Schema::dropIfExists('automation_trigger');
    }
}
