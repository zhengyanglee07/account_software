<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CurrenciesTableAddSeparatorTypeColumnAndChangeDisableDecimalToDecimalPlaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->renameColumn('disable_decimal','decimal_places');
            $table->integer('disable_decimal')->change();
            $table->string('separator_type')->after('disable_decimal')->default(',');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->renameColumn('decimal_places','disable_decimal');
            $table->boolean('decimal_places')->change();
            $table->dropColumn('separator_type');
        });
    }
}
