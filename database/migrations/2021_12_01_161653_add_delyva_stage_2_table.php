<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDelyvaStage2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delyva_quotations', function($table) {
            $table->string('service_company_name');
        });
        Schema::table('delyva_delivery_order_details', function($table) {
            // $table->string('status');
            $table->string('failed_reason')->nullable();
            $table->string('service_name')->nullable();
            $table->string('service_company_name')->nullable();
            $table->string('schedule_at')->nullable();
            $table->string('company_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('delyva_quotations', function($table) {
            $table->dropColumn('service_company_name');
        });
        Schema::table('delyva_delivery_order_details', function($table) {
            $table->dropColumn('service_name');
            $table->dropColumn('service_company_name');
            $table->dropColumn('schedule_at');
            // $table->dropColumn('status');
            $table->dropColumn('failed_reason');
            $table->dropColumn('company_id');
        });
    }
}
