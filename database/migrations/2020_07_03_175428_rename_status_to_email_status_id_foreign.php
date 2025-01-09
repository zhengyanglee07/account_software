<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameStatusToEmailStatusIdForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->bigInteger('email_status_id')->unsigned()->default(1)->after('schedule');
            $table->foreign('email_status_id')->references('id')->on('email_status')->onDelete('cascade');

            $table->dropColumn('status');
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
            $table->dropForeign('emails_email_status_id_foreign');
            $table->dropColumn('email_status_id');

            $table->string('status')->default('Draft')->after('schedule');
        });
    }
}
