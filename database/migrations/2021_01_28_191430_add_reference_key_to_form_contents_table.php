<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenceKeyToFormContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landing_page_form_contents', function (Blueprint $table) {
			$table->string('reference_key')->nullable()->after('landing_page_form_content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('landing_page_form_contents', function (Blueprint $table) {
            $table->dropColumn('reference_key');
        });
    }
}
