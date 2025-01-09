<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisitorIdColumnForLandingPageFormContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('landing_page_form_contents', function (Blueprint $table) {
            $table->bigInteger('visitor_id')->unsigned()->nullable()->after('processed_contact_id');
            $table->foreign('visitor_id')->references('id')->on('ecommerce_visitors')->onDelete('cascade');
        });

        Schema::table('landing_page_form_contents', function (Blueprint $table) {
            $table->dropColumn(['processed_contact_id']);
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
            $table->bigInteger('processed_contact_id')->unsigned()->nullable()->after('visitor_id');
        });
        
        Schema::disableForeignKeyConstraints();
        
        Schema::table('landing_page_form_contents', function (Blueprint $table) {
            $table->dropForeign(['visitor_id']);
        });
        Schema::table('landing_page_form_contents', function (Blueprint $table) {
            $table->dropColumn(['visitor_id']);
        });
        Schema::enableForeignKeyConstraints();
    }
}
