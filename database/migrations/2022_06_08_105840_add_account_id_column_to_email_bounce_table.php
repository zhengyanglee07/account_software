<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountIdColumnToEmailBounceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_bounces', function (Blueprint $table) {
            $table->bigInteger('account_id')->unsigned()->nullable()->after('id');
			$table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->string('type')->after('source_address')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_bounces', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn(['account_id', 'type']);
        });
    }
}
