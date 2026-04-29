<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function pos()
    {
        $products = Product::with('inventory')
            ->where('category', 'finished')
            ->get();
        $employees = Employee::all();

        return view('pos.index', compact('products', 'employees'));
    }

    public function index()
    {
        $sales = Sale::with(['employee', 'details.product'])
            ->orderByDesc('date')
            ->paginate(20);

        $totalSales   = Sale::whereDate('date', today())->sum('total_amount');
        $totalOrders  = Sale::whereDate('date', today())->count();
        $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        return view('sales.index', compact('sales', 'totalSales', 'totalOrders', 'avgOrderValue'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart_data'   => 'required|string',
            'employee_id' => 'nullable|exists:employees,id',
            'cash_paid'   => 'nullable|numeric|min:0',
        ]);

        $cartItems = json_decode($request->cart_data, true);

        if (empty($cartItems)) {
            return redirect()->route('pos')->with('error', 'Cart is empty.');
        }

        DB::transaction(function () use ($request, $cartItems) {
            $totalAmount = collect($cartItems)->sum(fn($item) => $item['price'] * $item['qty']);

            $sale = Sale::create([
                'employee_id'  => $request->employee_id,
                'date'         => now(),
                'total_amount' => $totalAmount,
                'cash_paid'    => $request->cash_paid ?? $totalAmount,
            ]);

            foreach ($cartItems as $item) {
                $inv = Inventory::where('product_id', $item['id'])->lockForUpdate()->first();

                if (!$inv || $inv->quantity_on_hand < $item['qty']) {
                    $name = Product::find($item['id'])?->product_name ?? "ID {$item['id']}";
                    throw new \Exception("Insufficient stock for: {$name}");
                }

                SaleDetail::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['id'],
                    'quantity'   => $item['qty'],
                    'unit_price' => $item['price'],
                ]);

                $inv->quantity_on_hand -= $item['qty'];
                $inv->save();
            }
        });

        return redirect()->route('pos')->with('success', 'Order completed successfully!');
    }

    public function show(Sale $sale)
    {
        $sale->load(['employee', 'details.product']);
        return view('sales.show', compact('sale'));
    }
}
