<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoProcessedAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_addresses', function (Blueprint $table) {
            $table->string('name')->nullable();           $table->string('country_code')->nullable();
            $table->string('state_code')->nullable();
            $table->tinyInteger('is_default_billing')->default(0);
            $table->tinyInteger('is_default_shipping')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processed_addresses', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('country_code');
            $table->dropColumn('state_code');
            $table->dropColumn('is_default_billing');
            $table->dropColumn('is_default_shipping');
        });
    }
}
