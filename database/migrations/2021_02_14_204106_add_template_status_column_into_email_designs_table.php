<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTemplateStatusColumnIntoEmailDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_designs', function (Blueprint $table) {
            $table->foreignId('template_status_id')->nullable()->after('email_design_type_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_designs', function (Blueprint $table) {
            $table->dropForeign(['template_status_id']);
        });
    }
}
