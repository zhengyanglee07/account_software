<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPageViewsAndUniquePageViewsInLandingpageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landingpage', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'settingElement',
                'previewElement',
                'affiliate_badge',
                'search_engine',
                'total_id_count',
                'page_views', 
                'unique_page_views',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('landingpage', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->text('settingElement')->nullable();
            $table->text('previewElement')->nullable();
            $table->string('affiliate_badge')->default('off');
            $table->string('search_engine')->default('off');
            $table->integer('total_id_count')->nullable();
            $table->integer('page_views')->default(0);
            $table->integer('unique_page_views')->default(0);
        });
    }
}
