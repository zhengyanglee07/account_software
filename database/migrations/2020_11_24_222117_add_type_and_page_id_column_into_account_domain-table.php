<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeAndPageIdColumnIntoAccountDomainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_domains', function (Blueprint $table) {
            $table->dropColumn('index');
            $table->boolean('is_subdomain')->default(0)->after('domain');
            $table->string("type")->nullable()->after("domain");
            $table->integer("page_id")->nullable()->after("domain");
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
            $table->dropColumn(['is_subdomain', 'type', 'page_id']);
        });
    }
}
