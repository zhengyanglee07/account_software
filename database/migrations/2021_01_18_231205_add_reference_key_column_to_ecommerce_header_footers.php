<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenceKeyColumnToEcommerceHeaderFooters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_header_footers', function (Blueprint $table) {
            $table->string('reference_key')->nullable()->after('pageLayout');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_header_footers', function (Blueprint $table) {
            $table->dropColumn('reference_key');
        });
    }
}
