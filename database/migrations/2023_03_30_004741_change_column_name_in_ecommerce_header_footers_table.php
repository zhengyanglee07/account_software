<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

class ChangeColumnNameInEcommerceHeaderFootersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_header_footers', function (Blueprint $table) {
            $table->json('design')->nullable()->after('elementArray');
            $table->renameColumn("elementArray", "element");
            $table->renameColumn("pageLayout", "section_width");
            $table->boolean('is_header')->default(true)->after('name');
            $table->boolean('is_active')->default(false)->after('is_header');
            $table->dropColumn("type");
            $table->dropColumn("total_id_count");
        });

        DB::table('ecommerce_header_footers')->where('active_status', 'active')->update(['is_active' => true]);
        DB::table('ecommerce_header_footers')->where('active_status', 'inactive')->update(['is_active' => false]);
        DB::table('ecommerce_header_footers')->where('sectionType', 'header')->update(['is_header' => true]);
        DB::table('ecommerce_header_footers')->where('sectionType', 'footer')->update(['is_header' => false]);

        Schema::table('ecommerce_header_footers', function (Blueprint $table) {
            $table->dropColumn("active_status");
            $table->dropColumn("sectionType");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_header_footers', function (Blueprint $table) {
            $table->renameColumn("element", "elementArray");
            $table->renameColumn("section_width", "pageLayout");
            $table->dropColumn('is_header');
            $table->dropColumn('is_active');
            $table->dropColumn("design");
            $table->string("type")->default("Draft")->after("name");
            $table->string("total_id_count")->default(0)->after("type");
            $table->string('active_status')->default('inactive')->after('name');
            $table->string('sectionType')->default('header')->after('name');
        });
    }
}
