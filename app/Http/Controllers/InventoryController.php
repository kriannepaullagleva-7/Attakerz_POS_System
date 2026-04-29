<?php

namespace App\Http\Controllers;

use App\Models\Production;
use Carbon\Carbon;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Employee;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
public function index()
{
    $inventories = Inventory::with('product')
        ->whereHas('product')
        ->orderBy('quantity_on_hand')
        ->get();

    $totalItems       = $inventories->count();
    $rawMaterialCount = $inventories->filter(fn($i) => $i->product->category === 'raw')->count();
    $finishedCount    = $inventories->filter(fn($i) => $i->product->category === 'finished')->count();
    $lowStockCount    = $inventories->filter(fn($i) => $i->quantity_on_hand <= ($i->border_point ?? 10))->count();

    $todayProductions = Production::whereDate('date', Carbon::today())->count();

    $todayOutput = Production::whereDate('date', Carbon::today())
        ->with('outputs')
        ->get()
        ->sum(fn($prod) => $prod->outputs->sum('quantity_produced'));

    $totalLogs = Production::count();

    $productions = Production::with(['employee', 'outputs'])
        ->orderBy('date', 'desc')
        ->get();

    $employees = Employee::orderBy('first_name')->get();

    $rawProducts = Product::where('category', 'raw')
        ->orderBy('product_name')
        ->get();

    $finishedProducts = Product::where('category', 'finished')
        ->orderBy('product_name')
        ->get();
        
    return view('inventory.index', compact(
        'inventories',
        'totalItems',
        'rawMaterialCount',
        'finishedCount',
        'lowStockCount',
        'todayProductions',
        'todayOutput',
        'totalLogs',
        'productions',
        'employees',
        'rawProducts',
        'finishedProducts'
    ));
    }

    public function quickAdd(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $inv = Inventory::firstOrCreate(
            ['product_id' => $request->product_id],
            ['quantity_on_hand' => 0]
        );
        $inv->increment('quantity_on_hand', $request->quantity);
        $inv->touch();

        return response()->json(['success' => true, 'message' => 'Inventory updated']);
    }
}