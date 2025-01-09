<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationAbandonCartTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_abandon_cart_triggers', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('automation_trigger_id')->unsigned();
            $table->foreign('automation_trigger_id')->references('id')->on('automation_trigger')->onDelete('cascade');

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
        Schema::dropIfExists('automation_abandon_cart_triggers');
    }
}
