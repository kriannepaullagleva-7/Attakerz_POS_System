<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->increments('production_id');
            $table->unsignedInteger('employee_id')->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->dateTime('date');
            $table->timestamps();
        });

        Schema::create('production_raw_materials', function (Blueprint $table) {
            $table->increments('raw_material_id');
            $table->unsignedInteger('production_id');
            $table->unsignedInteger('product_id');
            $table->integer('quantity_used');

            $table->foreign('production_id')->references('production_id')->on('productions')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('production_output', function (Blueprint $table) {
            $table->increments('output_id');
            $table->unsignedInteger('production_id');
            $table->unsignedInteger('product_id');
            $table->integer('quantity_produced');

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
