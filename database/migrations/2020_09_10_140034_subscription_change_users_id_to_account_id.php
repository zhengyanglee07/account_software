<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubscriptionChangeUsersIdToAccountId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // $table->dropForeign('subscriptions_users_id_foreign');
            // $table->dropColumn('users_id');
            //  $table->dropForeign('subscriptions_account_id_foreign');
            // $table->dropColumn('account_id');
            // $table->bigInteger('account_id')->unsigned()->after('id');
			// $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {

        });
    }
}
