<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMjmlColumnToEmailDesigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_designs', function (Blueprint $table) {
            $table->longText('mjml')->nullable()->after('html');
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
            $table->dropColumn('mjml');
        });
    }
}
