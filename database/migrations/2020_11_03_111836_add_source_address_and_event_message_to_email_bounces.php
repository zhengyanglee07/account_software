<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceAddressAndEventMessageToEmailBounces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_bounces', function (Blueprint $table) {
            $table->string('source_address')->after('email_address')->nullable();
            $table->longText('event_message')->after('source_address')->nullable();
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
            $table->dropColumn('source_address');
            $table->dropColumn('event_message');
        });
    }
}
