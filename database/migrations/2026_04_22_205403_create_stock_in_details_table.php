<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_in_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('stock_in_id');
            $table->foreign('stock_in_id')->references('id')->on('stock_ins')->cascadeOnDelete();
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('cost_per_unit', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_in_details');
    }
};
