<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddS3ColumnsIntoImageGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('image_galleries', function (Blueprint $table) {
            $table->renameColumn('image_name', 'name');
            $table->renameColumn('image_size', 'size');
            $table->renameColumn('image_width', 'width');
            $table->renameColumn('image_height', 'height');
            $table->renameColumn('image_path', 'local_path');
            $table->string('s3_name')->after('image_name');
            $table->string('s3_webp_name')->after('s3_name');
            $table->string('s3_url')->after('s3_webp_name');
            $table->string('s3_webp_url')->after('s3_url');
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
