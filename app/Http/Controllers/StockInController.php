<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = StockIn::all();
        return view('stock-in.index', compact('stocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = \App\Models\Supplier::all();
        $employees = \App\Models\Employee::all();
        $products = \App\Models\Product::all();
        return view('stock-in.create', compact('suppliers', 'employees', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'employee_id' => 'required|exists:employees,id',
            'items' => 'required|json'
        ]);

        $items = json_decode($request->items, true);

        DB::transaction(function () use ($request, $items) {

            $stock = StockIn::create([
                'supplier_id' => $request->supplier_id,
                'employee_id' => $request->employee_id,
                'date' => now(),
                'total_cost' => 0
            ]);

            $total = 0;

            foreach ($items as $item) {

                StockInDetail::create([
                    'stock_in_id' => $stock->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_per_unit' => $item['cost']
                ]);

                $total += $item['quantity'] * $item['cost'];

                $inv = Inventory::firstOrCreate(
                    ['product_id' => $item['product_id']],
                    ['quantity_on_hand' => 0]
                );
                $inv->increment('quantity_on_hand', $item['quantity']);
                $inv->touch();
            }

            $stock->update(['total_cost' => $total]);
        });

        return redirect('/stock-in')->with('success', 'Stock in created successfully');
    }

    public function show(StockIn $stockIn)
    {
        return view('stock-in.show', compact('stockIn'));
    }

    public function edit(StockIn $stockIn)
    {
        return view('stock-in.edit', compact('stockIn'));
    }

    public function update(Request $request, StockIn $stockIn)
    {
        //
    }

    public function destroy(StockIn $stockIn)
    {
        //
    }
}