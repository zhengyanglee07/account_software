<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetryInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retry_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->String('latest_invoice_id');
            $table->String('latest_invoice_payment_intent_status');
            $table->dateTime('expire_date');
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
        Schema::dropIfExists('retry_invoices');
    }
}
