<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PaymentApiTableAddAccountIdAndRefreshTokenColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_a_p_i_s', function (Blueprint $table) {
            $table->string('payment_account_id')->nullable()->after('enabled_at');
            $table->string('refresh_token')->nullable()->after('secret_key');
            $table->string('display_name2')->nullable()->after('display_name');
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
            $table->dropColumn('payment_account_id');
            $table->dropColumn('refresh_token');
            $table->dropColumn('live_mode');
        });
    }
}
