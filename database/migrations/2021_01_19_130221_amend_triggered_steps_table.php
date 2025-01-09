<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmendTriggeredStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('triggered_steps', function (Blueprint $table) {
            $table->dropColumn('extra_properties');
            $table->bigInteger('processed_contact_id')->unsigned()->after('automation_step_id')->nullable();
            $table->foreign('processed_contact_id')->references('id')->on('processed_contacts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('triggered_steps', function (Blueprint $table) {
            $table->longText('extra_properties')->nullable();

            $table->dropForeign('triggered_steps_processed_contact_id_foreign');
            $table->dropColumn('processed_contact_id');
        });
    }
}
