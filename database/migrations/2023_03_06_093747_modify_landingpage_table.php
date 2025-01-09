<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyLandingpageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landingpage', function (Blueprint $table) {
            $table->json('design')->nullable()->after('elementArray');
            $table->renameColumn('elementArray', 'element');
            $table->renameColumn('Page_name', 'name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('landingpage', function (Blueprint $table) {
            $table->dropColumn('design');
            $table->renameColumn('element', 'elementArray');
            $table->renameColumn('name', 'Page_name');
        });
    }
}
