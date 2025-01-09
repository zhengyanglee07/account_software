<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsInAllTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('all_templates', function (Blueprint $table) {
            $table->renameColumn('imagePath', 'image_path');
            $table->renameColumn('templateObject', 'template_objects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('all_templates', function (Blueprint $table) {
			$table->renameColumn('image_path', 'imagePath');
            $table->renameColumn('template_objects', 'templateObject');
        });
    }
}
