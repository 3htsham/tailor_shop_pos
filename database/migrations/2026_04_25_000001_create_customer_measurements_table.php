<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerMeasurementsTable extends Migration
{
    public function up()
    {
        Schema::create('customer_measurements', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->unsignedInteger('customer_id');    // matches customers.id (int unsigned)
            $table->unsignedBigInteger('sale_type_id'); // matches sale_types.id (bigint unsigned)
            $table->json('measurements');               // {"waist_length":"32","chest":"40"}
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('sale_type_id')->references('id')->on('sale_types')->onDelete('cascade');

            // One measurement record per customer per sale type
            $table->unique(['customer_id', 'sale_type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_measurements');
    }
}
