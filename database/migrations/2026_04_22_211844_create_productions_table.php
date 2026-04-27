<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Production table
        Schema::create('productions', function (Blueprint $table) {
            $table->id('production_id');
            $table->foreignId('employee_id')->nullable()->constrained('employees', 'id')->nullOnDelete();
            $table->dateTime('date')->nullable();
            $table->timestamps();
        });

        // Production raw materials
        Schema::create('production_raw_materials', function (Blueprint $table) {
            $table->id('raw_material_id');
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity_used', 10, 2)->nullable();

            $table->foreign('production_id')->references('production_id')->on('productions')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // Production output
        Schema::create('production_output', function (Blueprint $table) {
            $table->id('output_id');
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity_produced')->nullable();

            $table->foreign('production_id')->references('production_id')->on('productions')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // Add cash_paid column to sales if not exists
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'cash_paid')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('cash_paid', 10, 2)->nullable()->after('total_amount');
            });
        }

        // Add inventory columns if not exists (in case migration order differs)
        if (Schema::hasTable('inventories')) {
            if (!Schema::hasColumn('inventories', 'quantity_on_hand')) {
                Schema::table('inventories', function (Blueprint $table) {
                    $table->integer('quantity_on_hand')->default(0)->after('product_id');
                });
            }
            if (!Schema::hasColumn('inventories', 'border_point')) {
                Schema::table('inventories', function (Blueprint $table) {
                    $table->integer('border_point')->default(10)->after('quantity_on_hand');
                });
            }
            if (!Schema::hasColumn('inventories', 'last_updated')) {
                Schema::table('inventories', function (Blueprint $table) {
                    $table->timestamp('last_updated')->nullable()->after('border_point');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_output');
        Schema::dropIfExists('production_raw_materials');
        Schema::dropIfExists('productions');
    }
};