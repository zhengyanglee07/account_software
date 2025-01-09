<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePreviewElementTypeFromTextToLongText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landingpage', function (Blueprint $table) {
            $table->longText('previewElement')->nullable()->change();
            $table->longText('sidebarElement')->nullable()->change();
            $table->string('pageLayout',191)->default('fullWidth');
            $table->renameColumn('sidebarElement', 'elementArray');
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
            //
        });
    }
}
