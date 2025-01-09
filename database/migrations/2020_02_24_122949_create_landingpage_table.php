<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandingpageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('landingpage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('funnel_id')->unsigned();
            $table->foreign('funnel_id')->references('id')->on('funnel_page_main')->onDelete('cascade');
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->string('type')->default('Draft');
            $table->string('name')->nullable();
            $table->longText('sidebarElement')->nullable();
            $table->text('settingElement')->nullable();
            $table->text('previewElement')->nullable();
            $table->string('Page_name');
            $table->integer('index')->nullable();
            $table->string('path')->nullable();
            $table->integer('duplicated_count')->nullable();
            $table->string('SEO_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('fb_title')->nullable();
            $table->string('fb_description')->nullable();
            $table->text('fb_image')->nullable();
            $table->text('tracking_header')->nullable();
            $table->text('tracking_bodytop')->nullable();
            $table->string('affiliate_badge')->default('off');
            $table->string('search_engine')->default('off');
            $table->integer('total_id_count')->nullable();
            $table->integer('page_padding')->default(950);
            $table->string('reference_key')->nullable();
            $table->integer('page_views')->default(0);
            $table->integer('unique_page_views')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landingpage');
    }
}
