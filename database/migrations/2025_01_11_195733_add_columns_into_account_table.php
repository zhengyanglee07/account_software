<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::table('accounts', function (Blueprint $table) {
            $table->string('tax_id_no')->nullable();
            $table->string('reg_no_type')->nullable();
            $table->string('reg_no')->nullable();
            $table->string('old_reg_no')->nullable();
            $table->string('msic_code')->nullable();
            $table->string('msic_code_description')->nullable();
            $table->string('tourism_tax_reg_no')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('website_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('tax_id_no');
            $table->dropColumn('reg_no_type');
            $table->dropColumn('reg_no');
            $table->dropColumn('old_reg_no');
            $table->dropColumn('msic_code');
            $table->dropColumn('msic_code_description');
            $table->dropColumn('tourism_tax_reg_no');
            $table->dropColumn('contact_no');
            $table->dropColumn('contact_email');
            $table->dropColumn('website_url');
        });
    }
}
