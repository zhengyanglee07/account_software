<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLandingPageFormLabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landing_page_form_labels', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->bigInteger('landing_page_form_id')->unsigned()->nullable();
            $table->foreign('landing_page_form_id')->references('id')->on('landing_page_form')->onDelete('cascade');

            $table->text('landing_page_form_label')->nullable();
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
        Schema::dropIfExists('landing_page_form_labels');
    }
}
