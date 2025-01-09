<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndTypeToAllTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('all_templates', function (Blueprint $table) {
			$table->string('type')->default('page')->after('title');
			$table->string('status')->default('Draft')->after('title');
			$table->string('imagePath')->nullable()->change();
			$table->renameColumn('templateArray', 'templateObject');
			$table->json('tags')->nullable()->after('imagePath');
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
			$table->dropColumn(['type', 'status', 'tags']);
			$table->renameColumn('templateObject','templateArray')->change();
        });
    }
}
