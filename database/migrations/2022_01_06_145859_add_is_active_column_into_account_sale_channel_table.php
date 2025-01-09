<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveColumnIntoAccountSaleChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_sale_channel', function (Blueprint $table) {
            $table->string('is_active')->default(true)->constrained('sale_channels');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_sale_channel', function($table) {
            $table->dropColumn('is_active');
        });
    }
}
