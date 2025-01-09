<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PaymentAPISTableAddDisplayNameAndDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_a_p_i_s', function (Blueprint $table) {
            $table->String('display_name')->nullable()->after('account_id');
            $table->longText('description')->nullable()->after('display_name');
            $table->Boolean('enabled_at')->default(true)->after('description');

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
            $table->dropColumn('display_name');
            $table->dropColumn('description');
            $table->dropColumn('enabled_at');
        });
    }
}
