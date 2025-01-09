<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePageIdToTypeIdInAccountDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_domains', function (Blueprint $table) {
            $table->renameColumn('page_id', 'type_id');
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
            $table->renameColumn('type_id', 'page_id');
        });
    }
}
