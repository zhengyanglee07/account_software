<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTrackingFromStringToText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('funnel_page_main', function (Blueprint $table) {
            $table->text('tracking_header')->change();
            $table->text('tracking_body')->change();
            $table->string('text_color', 50)->change();
            $table->string('text_family', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('funnel_page_main', function (Blueprint $table) {
            //
        });
    }
}
