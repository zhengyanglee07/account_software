<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationRemoveTagActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_remove_tag_actions', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('automation_step_id')->unsigned();
            $table->foreign('automation_step_id')->references('id')->on('automation_steps')->onDelete('cascade');

            $table->bigInteger('processed_tag_id')->unsigned()->nullable();
            $table->foreign('processed_tag_id')->references('id')->on('processed_tags')->onDelete('set null');

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
        Schema::dropIfExists('automation_remove_tag_actions');
    }
}
