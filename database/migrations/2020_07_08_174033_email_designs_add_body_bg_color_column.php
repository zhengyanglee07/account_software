<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmailDesignsAddBodyBgColorColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_designs', function (Blueprint $table) {
            $table->string('body_bg_color')->default('#ffffff')->after('html');
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
            $table->dropColumn('body_bg_color');
        });
    }
}
