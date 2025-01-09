<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_options', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('account_id')->unsigned();
            $table->string('name')->nullable();
            $table->string('display_name');
            $table->string('type');
            $table->boolean('is_total_Charge')->default(false);
            $table->string('help_text')->nullable();
            $table->string('tool_tips')->nullable();
            $table->string('at_least')->nullable();
            $table->string('up_to')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_required')->default(false);
            $table->decimal('total_charge_amount')->default(0.00);
            $table->integer('sort_order');
            $table->timestamps();
        });
        Schema::table('product_options', function($table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_options');
    }
}
