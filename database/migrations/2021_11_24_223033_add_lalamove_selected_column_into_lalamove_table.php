<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLalamoveSelectedColumnIntoLalamoveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lalamoves', function (Blueprint $table) {
            $table->boolean('lalamove_selected')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lalamoves', function (Blueprint $table) {
            $table->dropColumn('lalamove_selected');
        });
    }
}
