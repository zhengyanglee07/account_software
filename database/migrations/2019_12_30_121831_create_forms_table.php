<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->string('formName')->nullable();
            $table->string('Url')->nullable();
            $table->string('text')->nullable();
            $table->string('email')->nullable();
            $table->string('textArea')->nullable();
            $table->string('mobileNumber')->nullable();
            $table->string('number')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            // $table->string('radio')->nullable();
            // $table->string('select')->nullable();
            // $table->string('checkbox')->nullable();
            // $table->string('acceptance')->nullable();
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
        Schema::dropIfExists('forms');
    }
}
