<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeTableAndColumnsNameOfUsersProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_products', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });

        Schema::table('users_products', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->renameColumn('productTitle', 'title');
            $table->renameColumn('productDescription', 'description');
            $table->renameColumn('productImagePath', 'image_url');
            $table->renameColumn('productImageCollection', 'image_collection');
            $table->renameColumn('productPrice', 'price');
            $table->renameColumn('productComparePrice', 'compare_price');
            $table->renameColumn('physicalProducts', 'type');
            $table->renameColumn('isTaxable', 'is_taxable');
            $table->renameColumn('SKU', 'sku');

            $table->dropColumn('availableStock');
            $table->dropColumn('continueSelling');
            $table->dropColumn('hasVariant');
            $table->dropColumn('isFreeShipping');
            $table->dropColumn('payment_type');
            $table->dropColumn('product_id');
        });

        Schema::rename('users_products', 'products');
    }

    // physical
    // virtual
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });

        Schema::rename('products', 'users_products');

        Schema::table('users_products', function (Blueprint $table) {
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->renameColumn('title', 'productTitle');
            $table->renameColumn('description', 'productDescription');
            $table->renameColumn('image_path', 'productImagePath');
            $table->renameColumn('image_collection', 'productImageCollection');
            $table->renameColumn('price', 'productPrice');
            $table->renameColumn('compare_price', 'productComparePrice');
            $table->renameColumn('type', 'physicalProducts');
            $table->renameColumn('is_taxable', 'isTaxable');
            $table->renameColumn('sku', 'SKU');


            $table->integer('availableStock')->nullable();
            $table->string('continueSelling')->nullable();
            $table->boolean('hasVariant')->default(false)->after('continueSelling');
            $table->boolean('isFreeShipping')->default(false)->after('reference_key');
            $table->string('payment_type')->default('subscription_and_otp');
            $table->string('product_id')->nullable();
        });
    }
}
