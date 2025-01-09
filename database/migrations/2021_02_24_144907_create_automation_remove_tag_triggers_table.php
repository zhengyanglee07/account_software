<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationRemoveTagTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_remove_tag_triggers', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('automation_trigger_id')->unsigned();
            $table->foreign('automation_trigger_id')->references('id')->on('automation_trigger')->onDelete('cascade');

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
        Schema::dropIfExists('automation_remove_tag_triggers');
    }
}
