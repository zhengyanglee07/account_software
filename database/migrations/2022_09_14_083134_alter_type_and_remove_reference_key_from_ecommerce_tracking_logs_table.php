<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTypeAndRemoveReferenceKeyFromEcommerceTrackingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_tracking_logs', function (Blueprint $table) {
            $table->dropColumn('reference_key');
            $table->boolean('is_conversion')->default(false)->after('value');
            $table->string('type')->default('store-page')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_tracking_logs', function (Blueprint $table) {
            $table->string('reference_key');
        });
    }
}
