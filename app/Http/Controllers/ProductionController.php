<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\ProductionRawMaterials;
use App\Models\ProductionOutput;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with(['employee', 'rawMaterials.product', 'outputs.product'])
            ->orderBy('date', 'desc')
            ->paginate(20);

        $employees = Employee::all();
        $rawProducts = Product::where('category', 'raw')->get();
        $finishedProducts = Product::where('category', 'finished')->get();

        $todayProductions = Production::whereDate('date', today())->count();
        $todayOutput = ProductionOutput::whereHas('production', fn($q) => $q->whereDate('date', today()))
            ->sum('quantity_produced');
        $totalLogs = Production::count();

        return view('production.index', compact(
            'productions', 'employees', 'rawProducts', 'finishedProducts',
            'todayProductions', 'todayOutput', 'totalLogs'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'          => 'required|exists:employees,id',
            'date'                 => 'required|date_format:Y-m-d\TH:i',
            'raw_product_id'       => 'required|array|min:1',
            'raw_product_id.*'     => 'required|exists:products,id',
            'raw_quantity'         => 'required|array|min:1',
            'raw_quantity.*'       => 'required|numeric|min:0.01',
            'output_product_id'    => 'required|array|min:1',
            'output_product_id.*'  => 'required|exists:products,id',
            'output_quantity'      => 'required|array|min:1',
            'output_quantity.*'    => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request) {
            $production = Production::create([
                'employee_id' => $request->employee_id,
                'date'        => $request->date,
            ]);

            $productionKey = $production->getKey();

            // Deduct raw materials from inventory
            foreach ($request->raw_product_id as $i => $productId) {
                $qty = $request->raw_quantity[$i];

                $inv = Inventory::where('product_id', $productId)->lockForUpdate()->first();

                if (!$inv || $inv->quantity_on_hand < $qty) {
                    $name = Product::find($productId)?->product_name ?? "ID {$productId}";
                    throw new \Exception("Insufficient stock for raw material: {$name}");
                }

                ProductionRawMaterials::create([
                    'production_id' => $productionKey,
                    'product_id'    => $productId,
                    'quantity_used' => $qty,
                ]);

                $inv->quantity_on_hand -= $qty;
                $inv->save();
            }

            // Add finished products to inventory
            foreach ($request->output_product_id as $i => $productId) {
                $qty = $request->output_quantity[$i];

                ProductionOutput::create([
                    'production_id'     => $productionKey,
                    'product_id'        => $productId,
                    'quantity_produced' => $qty,
                ]);

                $inv = Inventory::firstOrCreate(
                    ['product_id' => $productId],
                    ['quantity_on_hand' => 0, 'border_point' => 10]
                );
                $inv->quantity_on_hand += $qty;
                $inv->save();
            }
        });

        return redirect()->route('production.index')
            ->with('success', 'Production batch recorded and inventory updated successfully!');
    }

    public function show(Production $production)
    {
        $production->load(['employee', 'rawMaterials.product', 'outputs.product']);
        return view('production.show', compact('production'));
    }

    public function destroy(Production $production)
    {
        if (!\Carbon\Carbon::parse($production->date)->isToday()) {
            return redirect()->route('production.index')
                ->with('error', 'Only today\'s production logs can be deleted.');
        }

        DB::transaction(function () use ($production) {
            // Restore raw materials
            foreach ($production->rawMaterials as $rm) {
                $inv = Inventory::where('product_id', $rm->product_id)->first();
                if ($inv) {
                    $inv->quantity_on_hand += $rm->quantity_used;
                    $inv->save();
                }
            }
            // Deduct finished outputs
            foreach ($production->outputs as $out) {
                $inv = Inventory::where('product_id', $out->product_id)->first();
                if ($inv) {
                    $inv->quantity_on_hand -= $out->quantity_produced;
                    $inv->save();
                }
            }
            $production->delete();
        });

        return redirect()->route('production.index')->with('success', 'Production log reversed and deleted.');
    }
}
