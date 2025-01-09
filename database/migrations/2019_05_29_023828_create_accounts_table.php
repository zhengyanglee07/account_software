<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('accountRandomId')->nullable();
            // $table->bigInteger('user_id')->unsigned()->nullable();
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('shoptype_id')->unsigned()->nullable();
            $table->foreign('shoptype_id')->references('id')->on('shop_types')->onDelete('cascade');
            $table->string('api')->nullable();
            $table->string('password')->nullable();
            $table->string('domain')->nullable();
            $table->string('shopName')->nullable();
            $table->string('accountName')->nullable();
            $table->string('contactName')->nullable();
            $table->string('company')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('email')->nullable();
            $table->string('timeZone')->nullable();
            $table->string('date')->nullable();
            $table->string('country')->nullable();
            $table->string('currency')->nullable();
            $table->string('industry')->nullable();
            $table->string('emailFooter')->nullable();
            $table->string('status')->nullable();
            $table->string('emailSent')->default(0);
            $table->string('customDomain1')->nullable();
            $table->string('customDomain2')->nullable();
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
        Schema::dropIfExists('account');
    }
}
