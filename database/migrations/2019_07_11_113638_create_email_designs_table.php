<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_designs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->bigInteger('email_design_type_id')->unsigned()->nullable();
            $table->foreign('email_design_type_id')->references('id')->on('email_design_types');
            $table->text('name')->nullable(); // name for template, doesn't require in user edit
            $table->longText('preview')->nullable();
            $table->longText('html')->nullable();

//            $table->integer('itemNo')->nullable();
//            $table->text('script')->nullable();
//            $table->text('designElement')->nullable();
//            $table->longText('sidebarElement')->nullable();
//            $table->longText('settingElement')->nullable();
//            $table->text('previewContainer')->nullable();
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
        Schema::dropIfExists('email_designs');
    }
}
