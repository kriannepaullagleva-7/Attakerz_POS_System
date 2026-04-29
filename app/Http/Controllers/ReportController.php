<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // ─── TODAY ────────────────────────────────────────────────
        $todaySales  = Sale::whereDate('date', today())->sum('total_amount');
        $todayOrders = Sale::whereDate('date', today())->count();
        $todayAvg    = $todayOrders > 0 ? $todaySales / $todayOrders : 0;
        $todayItems  = SaleDetail::whereHas('sale', fn($q) => $q->whereDate('date', today()))->sum('quantity');

        // ─── MONTHLY SUMMARY ──────────────────────────────────────
        $monthTotal  = Sale::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('total_amount');
        $monthOrders = Sale::whereMonth('date', now()->month)->whereYear('date', now()->year)->count();
        $daysInMonth = now()->day;
        $monthAvg    = $daysInMonth > 0 ? $monthTotal / $daysInMonth : 0;

        $lastMonthTotal = Sale::whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('total_amount');
        $monthGrowth = $lastMonthTotal > 0
            ? round((($monthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1)
            : 0;

        // ─── DAILY CHART (14 days) ────────────────────────────────
        $dailyReport = collect();
        $dailyChartLabels = [];
        $dailyChartRevenue = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $rev = Sale::whereDate('date', $day)->sum('total_amount');
            $ord = Sale::whereDate('date', $day)->count();
            $itm = SaleDetail::whereHas('sale', fn($q) => $q->whereDate('date', $day))->sum('quantity');

            $dailyReport->push((object)[
                'sale_date'  => $day->toDateString(),
                'orders'     => $ord,
                'items_sold' => $itm,
                'revenue'    => (float) $rev,
            ]);

            $dailyChartLabels[]  = $day->format('M d');
            $dailyChartRevenue[] = (float) $rev;
        }

        // Reverse for table display (latest first)
        $dailyReport = $dailyReport->reverse()->values();

        // ─── TODAY PIE ────────────────────────────────────────────
        $todayPie = SaleDetail::select('product_id', DB::raw('SUM(quantity * unit_price) as revenue'))
            ->whereHas('sale', fn($q) => $q->whereDate('date', today()))
            ->with('product')
            ->groupBy('product_id')
            ->get();

        $todayPieLabels = $todayPie->map(fn($i) => $i->product->product_name ?? 'Unknown')->toArray();
        $todayPieData   = $todayPie->pluck('revenue')->map(fn($v) => (float) $v)->toArray();

        // ─── MONTHLY CHART (12 months) ────────────────────────────
        $monthlyReport = collect();
        $monthlyChartLabels  = [];
        $monthlyChartRevenue = [];

        for ($m = 11; $m >= 0; $m--) {
            $mo  = now()->subMonths($m);
            $rev = Sale::whereMonth('date', $mo->month)->whereYear('date', $mo->year)->sum('total_amount');
            $ord = Sale::whereMonth('date', $mo->month)->whereYear('date', $mo->year)->count();

            $monthlyReport->push((object)[
                'month'   => $mo->month,
                'year'    => $mo->year,
                'orders'  => $ord,
                'revenue' => (float) $rev,
            ]);

            $monthlyChartLabels[]  = $mo->format('M Y');
            $monthlyChartRevenue[] = (float) $rev;
        }

        // Latest first for table
        $monthlyReport = $monthlyReport->reverse()->values();

        // ─── PRODUCT PERFORMANCE ──────────────────────────────────
        $productPerformance = SaleDetail::select(
                'product_id',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(quantity * unit_price) as revenue'),
                DB::raw('AVG(unit_price) as avg_price')
            )
            ->whereHas('sale', fn($q) =>
                $q->whereMonth('date', now()->month)->whereYear('date', now()->year)
            )
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn($item) => (object)[
                'product_name' => $item->product->product_name ?? 'Unknown',
                'category'     => $item->product->category ?? '—',
                'unit'         => $item->product->unit ?? 'pcs',
                'units_sold'   => (int) $item->units_sold,
                'revenue'      => (float) $item->revenue,
                'avg_price'    => (float) $item->avg_price,
            ]);

        return view('reports.index', compact(
            'todaySales', 'todayOrders', 'todayAvg', 'todayItems',
            'monthTotal', 'monthOrders', 'monthAvg', 'monthGrowth',
            'dailyReport', 'dailyChartLabels', 'dailyChartRevenue',
            'todayPieLabels', 'todayPieData',
            'monthlyReport', 'monthlyChartLabels', 'monthlyChartRevenue',
            'productPerformance'
        ));
    }
}