<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->bigInteger('processed_contact_id')->unsigned()->nullable();
            $table->foreign('processed_contact_id')->references('id')->on('processed_contacts')->onDelete('cascade');
            $table->integer('maximum_indicator')->nullable();
            $table->integer('order_number');
            $table->string('additional_status')->nullable()->default("Open");;
            $table->string('fulfillment_status');
            $table->dateTime('fulfilled_at')->nullable();
            $table->string('payment_status');
            $table->datetime('paid_at')->nullable();
            $table->string('payment_references')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('currency')->default('MYR');
            $table->decimal('subtotal', 9, 2)->default(0.00);
            $table->decimal('shipping', 9, 2)->default(0.00);
            $table->decimal('taxes', 9, 2)->default(0.00);
            $table->decimal('total', 9, 2)->default(0.00);
            $table->decimal('paided_by_customer', 9, 2)->default(0.00);
            $table->decimal('refunded', 9, 2)->default(0.00);
            $table->string('notes')->nullable();
            $table->string('shipping_name')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_zipcode')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_phoneNumber')->nullable();
            $table->string('billing_name')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_zipcode')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_phoneNumber')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
