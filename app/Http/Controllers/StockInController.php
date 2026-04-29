<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Employee;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    public function index()
    {
        $stockIns = StockIn::with(['supplier', 'employee', 'details.product'])
            ->orderByDesc('date')
            ->paginate(20);

        $totalCostThisMonth = StockIn::whereMonth('date', now()->month)->sum('total_cost');
        $totalTransactions  = StockIn::count();
        $todayTransactions  = StockIn::whereDate('date', today())->count();

        return view('stock-in.index', compact('stockIns', 'totalCostThisMonth', 'totalTransactions', 'todayTransactions'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $employees = Employee::orderBy('first_name')->get();
        $products  = Product::orderBy('product_name')->get();

        return view('stock-in.create', compact('suppliers', 'employees', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'      => 'required|exists:suppliers,id',
            'employee_id'      => 'required|exists:employees,id',
            'date'             => 'required|date',
            'product_id'       => 'required|array|min:1',
            'product_id.*'     => 'required|exists:products,id',
            'quantity'         => 'required|array|min:1',
            'quantity.*'       => 'required|numeric|min:0.01',
            'cost_per_unit'    => 'required|array|min:1',
            'cost_per_unit.*'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $totalCost = 0;
            foreach ($request->product_id as $i => $pid) {
                $totalCost += $request->quantity[$i] * $request->cost_per_unit[$i];
            }

            $stockIn = StockIn::create([
                'supplier_id' => $request->supplier_id,
                'employee_id' => $request->employee_id,
                'date'        => $request->date,
                'total_cost'  => $totalCost,
            ]);

            foreach ($request->product_id as $i => $pid) {
                $qty = $request->quantity[$i];
                $cpu = $request->cost_per_unit[$i];

                StockInDetail::create([
                    'stock_in_id'   => $stockIn->id,
                    'product_id'    => $pid,
                    'quantity'      => $qty,
                    'cost_per_unit' => $cpu,
                ]);

                $inv = Inventory::firstOrCreate(
                    ['product_id' => $pid],
                    ['quantity_on_hand' => 0, 'border_point' => 10]
                );
                $inv->quantity_on_hand += $qty;
                $inv->save();
            }
        });

        return redirect()->route('stock-in.index')
            ->with('success', 'Stock-in recorded and inventory updated successfully!');
    }

    public function show(StockIn $stockIn)
    {
        $stockIn->load(['supplier', 'employee', 'details.product']);
        return view('stock-in.show', compact('stockIn'));
    }
}
