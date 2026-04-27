<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductionController;

// ─── Dashboard ────────────────────────────────────────────────
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ─── POS / Cashier ────────────────────────────────────────────
Route::get('/pos', [SaleController::class, 'pos'])->name('pos');

// ─── Sales ────────────────────────────────────────────────────
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

// ─── Products ─────────────────────────────────────────────────
Route::resource('products', ProductController::class);

// ─── Suppliers ────────────────────────────────────────────────
Route::resource('suppliers', SupplierController::class);

// ─── Stock In ─────────────────────────────────────────────────
Route::resource('stock-in', StockInController::class);

// ─── Inventory ────────────────────────────────────────────────
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory/quick-add', [InventoryController::class, 'quickAdd'])->name('inventory.quick-add');

// ─── Production ───────────────────────────────────────────────
Route::get('/production', [ProductionController::class, 'index'])->name('production.index');
Route::post('/production', [ProductionController::class, 'store'])->name('production.store');
Route::get('/production/{production}', [ProductionController::class, 'show'])->name('production.show');
Route::delete('/production/{production}', [ProductionController::class, 'destroy'])->name('production.destroy');

// ─── Reports ──────────────────────────────────────────────────
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');