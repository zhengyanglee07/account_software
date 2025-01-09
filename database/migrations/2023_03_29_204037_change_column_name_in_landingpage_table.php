<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class ChangeColumnNameInLandingpageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('landingpage', 'pages');
        Schema::table('pages', function (Blueprint $table) {
            $table->string('name')->after('funnel_id')->change();
            $table->boolean('is_landing')->default(true)->after('name');
            $table->boolean('is_published')->default(false)->after('is_landing');
            $table->string('page_padding')->default("full-width")->change();
            $table->renameColumn('page_padding', 'page_width');
            $table->renameColumn('SEO_title', 'seo_title');
            $table->renameColumn('meta_description', 'seo_meta_description');
        });

        DB::table('pages')->where('pageLayout', 'fullWidth')->update(['page_width' => 100]);
        DB::table('pages')->where('page_type', 'ecommerce')->update(['is_landing' => false]);
        DB::table('pages')->whereNull('page_type')->update(['is_landing' => true]);
        DB::table('pages')->where('type', 'Draft')->update(['is_published' => false]);
        DB::table('pages')->where('type', 'Publish')->update(['is_published' => true]);

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('pageLayout');
            $table->dropColumn('page_type');
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('pages', 'landingpage');
        Schema::table('landingpage', function (Blueprint $table) {
            $table->dropColumn('is_landing');
            $table->dropColumn('is_published');
            $table->renameColumn('page_width', 'page_padding');
            $table->renameColumn('seo_title', 'SEO_title');
            $table->renameColumn('seo_meta_description', 'meta_description');
            $table->string('pageLayout')->default('fullWidth')->after('page_width');
            $table->string('type')->default('Draft')->after('name');
            $table->string('page_type')->after('type');
        });
    }
}
