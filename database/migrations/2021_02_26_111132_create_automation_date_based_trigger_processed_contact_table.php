<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationDateBasedTriggerProcessedContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_date_based_trigger_processed_contact', function (Blueprint $table) {
            $table->bigInteger('automation_date_based_trigger_id')->unsigned();
            $table->foreign('automation_date_based_trigger_id', 'date_based_trigger_contact_date_based_trigger_id_foreign')->references('id')->on('automation_date_based_triggers')->onDelete('cascade');
            $table->bigInteger('processed_contact_id')->unsigned();
            $table->foreign('processed_contact_id', 'date_based_trigger_contact_contact_id_foreign')->references('id')->on('processed_contacts')->onDelete('cascade');
            $table->timestamp('triggered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('automation_date_based_trigger_processed_contact');
    }
}
