<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationDelaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_delays', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('automation_step_id')->unsigned();
            $table->foreign('automation_step_id')->references('id')->on('automation_steps')->onDelete('cascade');

            $table->integer('duration');
            $table->string('unit');

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
        Schema::dropIfExists('automation_delays');
    }
}
