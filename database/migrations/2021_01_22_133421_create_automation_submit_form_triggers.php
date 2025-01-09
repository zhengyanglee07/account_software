<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationSubmitFormTriggers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_submit_form_triggers', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('automation_trigger_id')->unsigned();
            $table->foreign('automation_trigger_id')->references('id')->on('automation_trigger')->onDelete('cascade');

            $table->bigInteger('landing_page_form_id')->unsigned()->nullable();
            $table->foreign('landing_page_form_id')->references('id')->on('landing_page_form')->onDelete('set null');

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
        Schema::dropIfExists('automation_submit_form_triggers');
    }
}
