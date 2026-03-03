<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleTypesTable extends Migration
{
    public function up()
    {
        Schema::create('sale_types', function (Blueprint $table) {
            $table->engine = 'InnoDB'; // Ye line engine fix kar degi
            $table->id();
            $table->string('name');
            $table->string('measurement_unit'); // cm or inches
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sale_types');
    }
}
