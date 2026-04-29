<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id('production_id');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->dateTime('date');
            $table->timestamps();
        });

        Schema::create('production_raw_materials', function (Blueprint $table) {
            $table->id('raw_material_id');
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity_used', 10, 2);

            $table->foreign('production_id')->references('production_id')->on('productions')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('production_output', function (Blueprint $table) {
            $table->id('output_id');
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity_produced', 10, 2);

            $table->foreign('production_id')->references('production_id')->on('productions')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_output');
        Schema::dropIfExists('production_raw_materials');
        Schema::dropIfExists('productions');
    }
};
