<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnableFpxToPaymentAPIS extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_a_p_i_s', function (Blueprint $table) {
            $table->Boolean('enable_fpx')->default(false)->after('enabled_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_a_p_i_s', function (Blueprint $table) {
            $table->dropColumn('enable_fpx');
        });
    }
}
