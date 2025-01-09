<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_domains', function (Blueprint $table) {
            $table->string('domain')->nullable()->change();
            $table->bigInteger('index')->nullable()->after('domain');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_domains', function (Blueprint $table) {
            //
            $table->dropColumn('index');
            $table->dropColumn('domain');
        });
    }
}
