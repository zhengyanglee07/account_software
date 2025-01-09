<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class MoveMessagesColsToEmails
 *
 * Note: messages cols in this file means cols from a deleted table messages, just ignore
 */
class MoveMessagesColsToEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->bigInteger('account_id')->unsigned()->nullable()->after('id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->string('type')->after('account_id');
            $table->string('name')->after('type');
            $table->datetime('schedule')->nullable()->after('name');
            $table->string('status')->after('schedule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign('emails_account_id_foreign');
            $table->dropColumn('account_id');
            $table->dropColumn('type');
            $table->dropColumn('name');
            $table->dropColumn('schedule');
            $table->dropColumn('status');
        });
    }
}
