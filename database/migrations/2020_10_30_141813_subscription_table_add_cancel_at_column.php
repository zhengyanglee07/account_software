<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubscriptionTableAddCancelAtColumn extends Migration
{
    public function up()
    {
        // Schema::table('subscriptions', function (Blueprint $table) {
        //     $table->dateTime('cancel_at')->nullable()->after('trial_end');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
        });
    }
}
