<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationSendEmailActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_send_email_actions', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('automation_step_id')->unsigned();
            $table->foreign('automation_step_id')->references('id')->on('automation_steps')->onDelete('cascade');

            $table->bigInteger('email_id')->unsigned()->nullable();
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('set null');

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
        Schema::dropIfExists('automation_send_email_actions');
    }
}
