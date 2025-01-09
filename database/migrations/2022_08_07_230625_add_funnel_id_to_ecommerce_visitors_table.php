<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFunnelIdToEcommerceVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->foreignId('funnel_id')->nullable()->constrained()->after('sales_channel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->dropForeign(['funnel_id']);
            $table->dropColumn('ecommerce_visitors_funnel_id_foreign');
        });
    }
}
