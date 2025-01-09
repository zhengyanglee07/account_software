<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcessedContactIdColumnIntoSentEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->bigInteger('processed_contact_id')->unsigned()->nullable();
            $table->foreign('processed_contact_id')->references('id')->on('processed_contacts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('sent_emails', function (Blueprint $table) {
            $table->dropForeign(['processed_contact_id']);
        });
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->dropColumn(['processed_contact_id']);
        });

        Schema::enableForeignKeyConstraints();

    }
}
