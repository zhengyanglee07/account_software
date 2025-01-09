<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDesignColumnToUserTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_templates', function (Blueprint $table) {
            $table->json('design')->nullable()->after('template_objects');
            $table->renameColumn('template_objects', 'element');
            $table->dropColumn("total_id_count");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_templates', function (Blueprint $table) {
            $table->renameColumn('element', 'template_objects');
            $table->dropColumn('design');
            $table->string("total_id_count")->default(0)->after("template_objects");
        });
    }
}
