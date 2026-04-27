<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::with('product')
            ->whereHas('product')
            ->orderBy('quantity_on_hand')
            ->get();

        $totalItems      = $inventories->count();
        $rawMaterialCount = $inventories->filter(fn($i) => $i->product->category === 'raw')->count();
        $finishedCount    = $inventories->filter(fn($i) => $i->product->category === 'finished')->count();
        $lowStockCount    = $inventories->filter(fn($i) => $i->quantity_on_hand <= ($i->border_point ?? 10))->count();

        return view('inventory.index', compact(
            'inventories', 'totalItems', 'rawMaterialCount', 'finishedCount', 'lowStockCount'
        ));
    }

    public function quickAdd(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $inv = Inventory::firstOrCreate(
                ['product_id' => $request->product_id],
                ['quantity_on_hand' => 0]
            );
            $inv->increment('quantity_on_hand', $request->quantity);
            $inv->touch();
        });

        return redirect()->route('inventory.index')
            ->with('success', 'Stock added successfully!');
    }
}