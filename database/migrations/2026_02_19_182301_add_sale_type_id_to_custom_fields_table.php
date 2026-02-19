<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleTypeIdToCustomFieldsTable extends Migration
{
    public function up()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_type_id')->nullable()->after('is_disable');
            $table->foreign('sale_type_id')->references('id')->on('sale_types')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropForeign(['sale_type_id']);
            $table->dropColumn('sale_type_id');
        });
    }
}
