<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeDefaultsToAutomations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('automations', function (Blueprint $table) {
            $table->bigInteger('automation_status_id')->unsigned()->default(1)->change();
            $table->boolean('repeat')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('automations', function (Blueprint $table) {
            $table->bigInteger('automation_status_id')->unsigned()->default(null)->change();
            $table->boolean('repeat')->default(0)->change();
        });
    }
}
