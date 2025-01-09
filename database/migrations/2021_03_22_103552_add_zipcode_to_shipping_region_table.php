<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZipcodeToShippingRegionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_regions', function (Blueprint $table) {
            $table->string('region_type')->after('country')->default('states');
            $table->json('state')->nullable()->change();
            $table->json('zipcodes')->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_regions', function (Blueprint $table) {
            $table->dropColumn('region_type');
            $table->dropColumn('zipcodes');
            $table->json('state')->nullable(false)->change();
        });
    }
}
