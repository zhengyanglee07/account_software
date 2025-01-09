<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditBalanceToProcessedContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_contacts', function (Blueprint $table) {
            $table->integer('credit_balance')->after('totalSpent')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('processed_contacts', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
    }
}
