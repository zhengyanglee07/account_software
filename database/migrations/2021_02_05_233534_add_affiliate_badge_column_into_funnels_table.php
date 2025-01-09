<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAffiliateBadgeColumnIntoFunnelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('funnel_page_main', function (Blueprint $table) {
			$table->boolean('has_affiliate_badge')->after('tracking_body')->default(true);
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
			$table->dropColumn('has_affiliate_badge');
        });
    }
}
