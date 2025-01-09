<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEasyParcelSelectedColumnIntoEasyParcelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('easy_parcels', function (Blueprint $table) {
            $table->boolean('easyparcel_selected')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('easy_parcels', function (Blueprint $table) {
            $table->dropColumn('easyparcel_selected');
        });
    }
}
