<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            // Link to a specific product row in the sale
            $table->unsignedInteger('product_sale_id')->nullable()->after('sale_id');
            $table->foreign('product_sale_id')
                  ->references('id')
                  ->on('product_sales')
                  ->onDelete('set null');

            // Which unit item of that product (1 … qty)
            $table->integer('unit_item_no')->nullable()->after('product_sale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_assignments', function (Blueprint $table) {
            $table->dropForeign(['product_sale_id']);
            $table->dropColumn(['product_sale_id', 'unit_item_no']);
        });
    }
};
