<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeEmailDesignsAccountIdNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_designs', function (Blueprint $table) {
            $table->bigInteger('account_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Note: this rollback will not work if there's already one or more
        //       rows with account_id = NULL. Change all those NULL to a
        //       default value first
        Schema::table('email_designs', function (Blueprint $table) {
            $table->bigInteger('account_id')->unsigned()->nullable(false)->change();
        });
    }
}
