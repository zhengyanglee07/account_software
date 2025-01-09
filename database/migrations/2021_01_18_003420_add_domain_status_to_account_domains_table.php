<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDomainStatusToAccountDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_domains', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('is_subdomain');
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
            $table->dropColumn('is_verified');
        });
    }
}
