<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ─── TODAY'S STATS ────────────────────────────────────────
        $todaySales = Sale::whereDate('date', today())->sum('total_amount');
        $todayOrders = SaleDetail::whereHas('sale', fn($q) => $q->whereDate('date', today()))->sum('quantity');

        // ─── YESTERDAY FOR TREND ──────────────────────────────────
        $yesterdaySales = Sale::whereDate('date', now()->subDay()->toDateString())->sum('total_amount');
        $salesTrend = $yesterdaySales > 0
            ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1)
            : 0;

        $yesterdayOrders = SaleDetail::whereHas('sale', fn($q) => $q->whereDate('date', now()->subDay()->toDateString()))->sum('quantity');
        $ordersTrend = $yesterdayOrders > 0
            ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100, 1)
            : 0;

        // ─── WEEKLY AVERAGE ────────────────────────────────────────
        $weeklyTotal = Sale::whereBetween('date', [now()->startOfWeek(), now()])->sum('total_amount');
        $daysPassed = max(1, today()->dayOfWeek ?: 7);
        $weeklyAvg = $weeklyTotal / $daysPassed;

        $lastWeekTotal = Sale::whereBetween('date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->sum('total_amount');
        $weeklyGrowth = $lastWeekTotal > 0
            ? round((($weeklyTotal - $lastWeekTotal) / $lastWeekTotal) * 100, 1)
            : 0;

        // ─── LOW STOCK ─────────────────────────────────────────────
        $lowStockCount = Inventory::where('quantity_on_hand', '<=', 10)->count();
        $lowStockItems = Inventory::with('product')
            ->where('quantity_on_hand', '<=', 10)
            ->orderBy('quantity_on_hand')
            ->get()
            ->map(function ($inv) {
                return (object)[
                    'product_name'   => $inv->product->product_name,
                    'unit'           => $inv->product->unit,
                    'quantity_on_hand' => $inv->quantity_on_hand,
                    'border_point'   => $inv->border_point ?? 10,
                ];
            });

        // ─── CHART DATA (7 days) ───────────────────────────────────
        $chartLabels = [];
        $revenueData = [];
        $itemsData = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $chartLabels[] = $day->format('D');
            $revenueData[] = (float) Sale::whereDate('date', $day)->sum('total_amount');
            $itemsData[] = (int) SaleDetail::whereHas('sale', fn($q) => $q->whereDate('date', $day))->sum('quantity');
        }

        // ─── BEST SELLERS ──────────────────────────────────────────
        $bestSellers = SaleDetail::select('product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(quantity * unit_price) as total_revenue')
            )
            ->with('product')
            ->whereHas('sale', fn($q) => $q->whereBetween('date', [now()->startOfMonth(), now()]))
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(fn($item) => (object)[
                'product_name'  => $item->product->product_name ?? 'Unknown',
                'total_qty'     => $item->total_qty,
                'total_revenue' => $item->total_revenue,
            ]);

        return view('welcome', compact(
            'todaySales', 'todayOrders', 'weeklyAvg', 'lowStockCount',
            'salesTrend', 'ordersTrend', 'weeklyGrowth',
            'chartLabels', 'revenueData', 'itemsData',
            'bestSellers', 'lowStockItems'
        ));
    }
}