<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class AddDesignColumnToTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('all_templates', function (Blueprint $table) {
            $table->json('design')->nullable()->after('template_objects');
            $table->boolean('is_published')->default(false)->after('title');
            $table->renameColumn('template_objects', 'element');
            $table->renameColumn('title', 'name');
            $table->dropColumn("total_id_count");
        });

        DB::table('all_templates')->where('status', 'Draft')->update(['is_published' => false]);
        DB::table('all_templates')->where('status', 'Publish')->update(['is_published' => true]);

        Schema::table('all_templates', function (Blueprint $table) {
            $table->dropColumn('status');
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
            $table->renameColumn('element', 'template_objects');
            $table->renameColumn('name', 'title');
            $table->dropColumn('is_published');
            $table->dropColumn('design');
            $table->string("total_id_count")->default(0)->after("template_objects");
            $table->string('status')->default('Draft')->after('name');
        });
    }
}
