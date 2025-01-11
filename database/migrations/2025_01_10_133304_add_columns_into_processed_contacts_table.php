<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsIntoProcessedContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('processed_contacts', function (Blueprint $table) {
            $table->string('entity_type')->nullable();
            $table->string('reg_no')->nullable();
            $table->string('old_reg_no')->nullable();
            $table->string('sst_reg_no')->nullable();
            $table->string('reg_no_type')->nullable();
            $table->string('tax_id_no')->nullable();
            $table->text('remarks')->nullable();
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
            $table->dropColumn('entity_type');
            $table->dropColumn('reg_no');
            $table->dropColumn('old_reg_no');
            $table->dropColumn('sst_reg_no');
            $table->dropColumn('reg_no_type');
            $table->dropColumn('tax_id_no');
            $table->dropColumn('remarks');
        });
    }
}
