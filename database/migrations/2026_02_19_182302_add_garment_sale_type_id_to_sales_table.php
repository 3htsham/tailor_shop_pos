<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGarmentSaleTypeIdToSalesTable extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('garment_sale_type_id')->nullable();
            $table->foreign('garment_sale_type_id')->references('id')->on('sale_types')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['garment_sale_type_id']);
            $table->dropColumn('garment_sale_type_id');
        });
    }
}
