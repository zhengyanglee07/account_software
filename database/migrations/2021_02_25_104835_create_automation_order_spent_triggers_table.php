<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationOrderSpentTriggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_order_spent_triggers', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('automation_trigger_id')->unsigned();
            $table->foreign('automation_trigger_id')->references('id')->on('automation_trigger')->onDelete('cascade');

            $table->string('operator');
            $table->decimal('spent', 9, 2)->default(0.00);

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
        Schema::dropIfExists('automation_order_spent_triggers');
    }
}
