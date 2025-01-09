<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAllS3ColumnsToNullableInImageGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('image_galleries', function (Blueprint $table) {
            $table->string('s3_name')->nullable()->change();
            $table->string('s3_webp_name')->nullable()->change();
            $table->string('s3_url')->nullable()->change();
            $table->string('s3_webp_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('image_galleries', function (Blueprint $table) {
            //
        });
    }
}
