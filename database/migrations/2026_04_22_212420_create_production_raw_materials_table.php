<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if already created by 2026_04_22_211844_create_productions_table.php
        if (!Schema::hasTable('production_raw_materials')) {
            Schema::create('production_raw_materials', function (Blueprint $table) {
                $table->id('raw_material_id');
                $table->unsignedBigInteger('production_id');
                $table->unsignedBigInteger('product_id');
                $table->integer('quantity_used');
                
                $table->foreign('production_id')->references('production_id')->on('productions')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_raw_materials');
    }
};
    
