<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveSubscriptionIdAndStatusFromUsersToAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('subscriptions', function (Blueprint $table) {
		// 	$table->bigInteger('users_id')->unsigned()->nullable()->change();
		// });
		Schema::table('accounts', function (Blueprint $table) {
			$table->bigInteger('subscription_plan_id')->unsigned()->nullable()->after('shoptype_id');
			$table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->onDelete('cascade');
			$table->string('subscription_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            //
        });
    }
}
