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
        Schema::create('employee_balance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('employee_id');
            $table->decimal('amount', 10, 2);
            $table->text('note')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_balance_adjustments');
    }
};
