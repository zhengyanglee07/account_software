<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->String('subscription_id');
            $table->String('invoice_number')->unique();
            $table->String('plan_name')->nullable();
            $table->String('description');
            $table->boolean('is_proration')->default(false);
            $table->dateTime('plan_start');
            $table->dateTime('plan_end');
            $table->integer('quantity');
            $table->decimal('total');
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('companyName');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('zipCode');
            $table->string('status')->default('pending');
            $table->string('reference_key')->nullable();
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
        Schema::dropIfExists('subscription_invoices');
    }
}
