<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteToImportantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('all_templates', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('funnel_page_main', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('processed_contacts', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('landingpage', function (Blueprint $table) {
            $table->softDeletes();
        });
        // Schema::table('landing_save_templates', function (Blueprint $table) {
        //     $table->softDeletes();
        // });
        Schema::table('variants', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('variant_details', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('variant_values', function (Blueprint $table) {
            $table->softDeletes();
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
            $table->dropSoftDeletes();
        });
        Schema::table('funnel_page_main', function (Blueprint $table) {
             $table->dropSoftDeletes();
        });
        Schema::table('orders', function (Blueprint $table) {
             $table->dropSoftDeletes();
        });
        Schema::table('processed_contacts', function (Blueprint $table) {
             $table->dropSoftDeletes();
        });
        Schema::table('users', function (Blueprint $table) {
             $table->dropSoftDeletes();
        });
        Schema::table('landingpage', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        // Schema::table('landing_save_templates', function (Blueprint $table) {
        //     $table->dropSoftDeletes();
        // });
        Schema::table('variants', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('variant_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('variant_values', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });


    }
}
