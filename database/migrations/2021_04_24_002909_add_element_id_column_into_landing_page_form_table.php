<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddElementIdColumnIntoLandingPageFormTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landing_page_form', function (Blueprint $table) {
            $table->string('element_id')->nullable()->after('landing_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('landing_page_form', function (Blueprint $table) {
            $table->dropColumn('element_id');
        });
    }
}
