<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFunnelIdToNullableInLandingpageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landingpage', function (Blueprint $table) {
            $table->dropForeign('landingpage_funnel_id_foreign');
            $table->bigInteger('funnel_id')->nullable()->unsigned()->change();
            $table->foreign('funnel_id')->references('id')->on('funnel_page_main')->onDelete('cascade');
    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nullable_in_landingpage', function (Blueprint $table) {
            //
        });
    }
}
